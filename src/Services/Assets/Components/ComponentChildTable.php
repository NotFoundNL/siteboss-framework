<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Models\AssetItem;
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
        $table = Table::whereTable($this->properties()->allowedBlocks)->first();

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

        $table = Table::whereTable($this->properties()->allowedBlocks)->first();

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

    public function afterSave(): void
    {
        $parentId = $this->recordId;
        $foreignKey = $this->getForeignTable();

        $assetItem = new AssetItem();
        $assetItem->type = 'text';
        $assetItem->internal = $foreignKey;
        $parentIdComponent = new ComponentStaticValue($this->assetService, $assetItem);
        $parentIdComponent->setStaticValue($parentId ?? 0);

        $assetItem = new AssetItem();
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
     * getChildren
     *
     * Get child rows from the linked table for the current record
     */
    private function getChildren(): Collection
    {
        return DB::table($this->properties()->allowedBlocks)->where($this->getForeignTable(), $this->recordId)->where('deleted_at', null)->orderBy('order')->get();
    }

    private function getForeignTable()
    {
        return ($this->assetType->value == 'page') ? 'page_id' : rtrim($this->assetModel->table, 's').'_id';
    }
}
