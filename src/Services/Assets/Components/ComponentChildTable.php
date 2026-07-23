<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputContentBlocks;
use NotFound\Framework\Models\AssetItem;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\TableService;

class ComponentChildTable extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function validate($newValue): bool
    {
        if ($newValue === null) {
            return true;
        }

        if (! is_array($newValue)) {
            return false;
        }

        foreach ($newValue as $block) {
            if (! isset($block['items']) || ! isset($block['tableId'])) {
                Log::withContext(['value' => $newValue])->warning('[ContentBlock] Wrong value submitted');

                return false;
            }
        }

        return true;
    }

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputContentBlocks($this->assetItem->internal, $this->assetItem->name);
    }

    public function setValueFromStorage(mixed $value): bool
    {
        $table = Table::whereTable($this->properties()->childTable)->first();

        $contentBlocksWithValues = new Collection;

        $children = $this->getChildren();

        foreach ($children as $child) {
            $ts = new TableService($table, $this->assetService->getLang(), $child->id);

            $tableValues = new \stdClass;
            $fieldComponents = $ts->getComponents();
            foreach ($fieldComponents as $fieldComponent) {
                $tableValues->{$fieldComponent->assetItem->internal} = $fieldComponent->getDisplayValue();
            }

            $contentBlocksWithValues->add($fieldComponents);
        }

        $this->currentValue = $contentBlocksWithValues;

        return true;
    }

    public function getDisplayValue(): array
    {
        // BUG: This should use less extensive logic

        $contentBlocks = $this->getChildren();

        $table = Table::whereTable($this->properties()->childTable)->first();
        $contentBlocksWithValues = [];
        foreach ($contentBlocks as $contentBlock) {
            /** @var CmsContentBlocks $contentBlock */
            $ts = new TableService($table, $this->assetService->getLang(), $contentBlock->id);
            $fieldComponents = $ts->getComponents();

            $tableValues = new \stdClass;
            foreach ($fieldComponents as $fieldComponent) {
                $tableValues->{$fieldComponent->assetItem->internal} = $fieldComponent->getDisplayValue();

            }
            $contentBlocksWithValues[] = $tableValues;
        }

        return $contentBlocksWithValues;

    }

    protected function customProperties(): object
    {
        // We try to emulate being a content block to AutoLayout
        return (object) ['allowedBlocks' => $this->properties()->childTable];
    }

    public function afterSave(): void
    {
        $parentId = $this->recordId;
        $foreignKey = $this->getForeignKey();

        $assetItem = new AssetItem;
        $assetItem->type = 'text';
        $assetItem->internal = $foreignKey;
        $parentIdComponent = new ComponentStaticValue($this->assetService, $assetItem);
        $parentIdComponent->setStaticValue($parentId ?? 0);

        $assetItem = new AssetItem;
        $assetItem->type = 'text';
        $assetItem->internal = 'order';
        $orderComponent = new ComponentStaticValue($this->assetService, $assetItem);

        $deleted = 0;

        foreach ($this->newValue as $block) {
            // new values are given a string(for frontend purposes). So set them to null
            if (is_string($block['recordId'])) {
                $block['recordId'] = null;
            }

            // Block is deleted but not in the database, so skip it
            if ($block['deleted'] === true && $block['recordId'] == null) {
                continue;
            }

            $block['order'] -= $deleted;

            /** @var Table $table */
            $table = Table::where('id', $block['tableId'])->first();
            $ts = new TableService($table, $this->assetService->getLang(), $block['recordId']);

            $orderComponent->setStaticValue($block['order']);
            $ts->addCustomComponent('order', $orderComponent);

            $ts->validate(new Request($block['items']));

            if ($block['recordId'] === null) {
                $ts->addCustomComponent($foreignKey, $parentIdComponent);
                $ts->create();
            } elseif ($block['deleted']) {
                $deleted++;
                $ts->delete();
            } else {
                $ts->update();
            }
        }
    }

    public function setNewValue(mixed $value): void
    {
        if (! is_array($value)) {
            $this->newValue = [];

            return;
        }

        $this->newValue = $value;
    }

    /**
     * The children of this record are purged, including the ones that were
     * deleted earlier. They are purged through the TableService, so their
     * files and their own children are removed as well.
     */
    public function purge(): bool
    {
        $table = Table::whereTable($this->properties()->childTable)->first();

        if ($table === null) {
            Log::withContext(['childTable' => $this->properties()->childTable])
                ->warning('[ChildTable] Could not purge children, table not found');

            return false;
        }

        $succeeded = true;
        foreach ($this->allChildren() as $child) {
            $ts = new TableService($table, $this->assetService->getLang(), $child->id);

            if (! $ts->purge()) {
                $succeeded = false;
            }
        }

        return $succeeded;
    }

    /**
     * getChildren
     *
     * Get child rows from the linked table for the current record
     */
    private function getChildren(): Collection
    {
        return DB::table($this->properties()->childTable)->where($this->getForeignKey(), $this->recordId)->where('deleted_at', null)->orderBy('order')->get();
    }

    /**
     * allChildren
     *
     * Get every child row of the current record, including the deleted ones
     */
    private function allChildren(): Collection
    {
        return DB::table($this->properties()->childTable)->where($this->getForeignKey(), $this->recordId)->orderBy('order')->get();
    }

    private function getForeignKey()
    {
        $prefix = (isset($this->properties()->prefix)) ? $this->properties()->prefix.'_' : '';

        return ($this->assetType->value == 'page') ? 'page_id' : ltrim(rtrim($this->assetModel->table, 's').'_id', $prefix);
    }
}
