<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputContentBlocks;

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
        $table = Table::whereTable($this->properties()->foreignTable)->first();

        $contentBlocksWithValues = new Collection();

        $children = $this->getChildren();

        foreach ($children as $child) {
            $ts = new TableService($table, $this->assetService->getLang(), $child->id);

            $tableValues = new \stdClass();
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
        $contentBlocks = $this->getChildren();

        $table = Table::whereTable($this->properties()->foreignTable)->first();

        $contentBlocksWithValues = [];
        foreach ($contentBlocks as $contentBlock) {
            /** @var CmsContentBlocks $contentBlock */
            $ts = new TableService($table, $this->assetService->getLang(), $contentBlock->id);
            $fieldComponents = $ts->getComponents();

            $tableValues = new \stdClass();
            foreach ($fieldComponents as $fieldComponent) {
                $tableValues->{$fieldComponent->assetItem->internal} = $fieldComponent->getDisplayValue();
            }
            $contentBlocksWithValues[] = (object) [
                'type' => $fieldComponent->assetItem->table->url,
                'values' => $tableValues,
            ];
        }

        return $contentBlocksWithValues;
    }

    public function beforeSave(): void
    {
        $newValue = [];
        foreach ($this->newValue as $block) {
            $block['items'][$this->properties()->foreignKey] = $this->recordId ?? 1;

            $newValue[] = $block;
        }
        $this->setNewValue($newValue);
    }

    public function afterSave(): void
    {
        $tables = Table::all();
        foreach ($this->newValue as $block) {
            // new values are given a string(for frontend purposes). So set them to null
            if (is_string($block['recordId'])) {
                $block['recordId'] = null;
            }

            // Block is deleted but not in the database, so skip it
            if ($block['deleted'] === true && $block['recordId'] == null) {
                continue;
            }

            /** @var Table $table */
            $table = $tables->where('id', $block['tableId'])->first();
            $ts = new TableService($table, $this->assetService->getLang(), $block['recordId']);
            if (! isset($block['items'][$this->properties()->foreignKey])) {
                dd($block);
            }
            // Recursively update the table that is set inside this component

            if ($block['recordId'] === null) {
                $recordId = $ts->create();
            } else {
                $recordId = $ts->update();
            }
            $block['items'][$this->properties()->foreignKey] = $recordId;

            $ts->validate(new Request($block['items']));
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

    private function getChildren()
    {
        return DB::table($this->properties()->foreignTable)->where($this->properties()->foreignKey, $this->recordId)->where('deleted_at', null)->get();
    }
}
