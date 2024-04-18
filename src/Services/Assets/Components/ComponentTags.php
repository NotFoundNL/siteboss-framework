<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Facades\DB;
use NotFound\Framework\Services\Legacy\StatusColumn;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputTags;

class ComponentTags extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $p = $this->properties();
        $LayoutInputTags = new LayoutInputTags($this->assetItem->internal, $this->assetItem->name);

        if (! (isset($this->assetItem->properties->lazyLoad) && $this->assetItem->properties->lazyLoad == true)) {
            $tags = $this->getTagsData();
            foreach ($tags as $item) {
                $LayoutInputTags->addItem($item->{$p->foreignTagId}, $item->label);
            }
        } else {
            // TODO: language support?
            $endPoint = sprintf('table/%s/%d/nl/%s', $this->assetModel->url, $this->recordId, $this->assetItem->internal);
            $LayoutInputTags->setEndpoint($endPoint);
        }

        return $LayoutInputTags;
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    private function getTagsData()
    {
        $properties = $this->properties();
        $foreignTable = $this->removeDatabasePrefix($properties->foreignTable);

        $builder = DB::table($foreignTable)
            ->select($foreignTable.'.'.$properties->foreignTagId, $foreignTable.'.'.$properties->foreignDisplayColumn.' AS label');

        if (isset($properties->useStatus) && $properties->useStatus == true) {
            $builder = StatusColumn::wherePublished($builder, $foreignTable);
        }

        return $builder->get();
    }

    public function getCurrentValue()
    {
        $p = $this->properties();
        $foreignTable = $this->removeDatabasePrefix($p->foreignTable);
        $linkTable = $this->removeDatabasePrefix($p->linkTable);

        if (isset($p->tagsSortable) && $p->tagsSortable === true) {
            return DB::table($foreignTable)
                ->join($linkTable, $foreignTable.'.'.$p->foreignTagId, '=', $linkTable.'.'.$p->linkTagId)
                ->where($p->linkItemId, $this->recordId)
                ->orderBy($linkTable.'.order')
                ->get([$foreignTable.'.'.$p->foreignTagId.' AS id', $foreignTable.'.'.$p->foreignDisplayColumn.' AS label'])
                ->toArray();
        }

        return DB::table($foreignTable)
            ->join($linkTable, $foreignTable.'.'.$p->foreignTagId, '=', $linkTable.'.'.$p->linkTagId)
            ->where($p->linkItemId, $this->recordId)
            ->get([$foreignTable.'.'.$p->foreignTagId.' AS id', $foreignTable.'.'.$p->foreignDisplayColumn.' AS label'])->toArray();
    }

    /**
     * Gets the content for the table overview, this is usually a string.
     */
    public function getTableOverviewContent(): LayoutTableColumn
    {
        // convert to array with only the value of the label
        $values = collect($this->getCurrentValue())->map(function ($item) {
            return $item->label;
        });

        return new LayoutTableColumn(implode(', ', $values->toArray()));
    }

    /**
     * This function is for doing additional actions while saving, or - when not using the
     * default storage mechanism - doing custom stuff.
     *
     * @return void
     */
    public function save()
    {
        $p = $this->properties();
        if (isset($p->tagsSortable) && $p->tagsSortable === true) {
            $this->saveSortable();
        } else {
            $this->saveNonSortable();
        }
    }

    /**
     * saveSortable
     *
     * Sort the tags by the order they are in the array.
     */
    private function saveSortable(): void
    {
        $currentValues = $this->getCurrentValue();
        $newValues = $this->newValue;

        // Delete all tags that are not in the new array
        foreach ($currentValues as $currentValue) {
            if (! in_array($currentValue->id, $newValues)) {
                $this->deleteTag($currentValue->id);
            }
        }

        $order = 1;
        foreach ($newValues as $newValue) {
            // Upsert the new values with order
            $this->upsertTag($order++, $newValue);
        }
    }

    private function saveNonSortable(): void
    {
        $currentValues = $this->getCurrentValue();
        $newValues = $this->newValue;
        foreach ($currentValues as $currentValue) {
            if (! in_array($currentValue->id, $newValues)) {
                $this->deleteTag($currentValue->id);
            } else {
                $newValues = array_diff($newValues, [$currentValue->id]);
            }
        }

        foreach ($newValues as $newValue) {
            $this->addTag($newValue);
        }
    }

    public function deleteTag($id)
    {
        $p = $this->properties();
        $linkTable = $this->removeDatabasePrefix($p->linkTable);

        return DB::table($linkTable)
            ->where($p->linkItemId, $this->recordId)
            ->where($p->linkTagId, $id)
            ->delete();
    }

    public function addTag($id)
    {
        $p = $this->properties();
        $linkTable = $this->removeDatabasePrefix($p->linkTable);

        return DB::table($linkTable)->insert([
            [$p->linkItemId => $this->recordId, $p->linkTagId => $id],
        ]);
    }

    private function upsertTag($order, $id)
    {
        $p = $this->properties();
        $linkTable = $this->removeDatabasePrefix($p->linkTable);

        return DB::table($linkTable)->upsert(
            [$p->linkItemId => $this->recordId, $p->linkTagId => $id, 'order' => $order],
            [$p->linkItemId, $p->linkTagId],
            ['order']
        );
    }

    public function asyncGetRequest()
    {
        $requestValues = request()->validate([
            'q' => 'string|required|min:3',
        ]);

        $properties = $this->properties();
        if (strlen($requestValues['q']) < 3) {
            return (object) ['results' => []];
        }

        $queryString = $requestValues['q'];
        $foreignTable = $this->removeDatabasePrefix($properties->foreignTable);

        $builder = DB::table($foreignTable)
            ->select($foreignTable.'.'.$properties->foreignTagId, $foreignTable.'.'.$properties->foreignDisplayColumn.' AS label')
            ->where($foreignTable.'.'.$properties->foreignDisplayColumn, 'LIKE', '%'.$queryString.'%');

        if (isset($properties->useStatus) && $properties->useStatus == true) {
            $builder = StatusColumn::wherePublished($builder, $foreignTable);
        }

        return (object) [
            'results' => $builder->get(),
        ];
    }
}
