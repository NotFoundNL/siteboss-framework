<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use NotFound\Framework\Models\CmsContentBlocks;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\TableService;
use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputContentBlocks;

/**
 * Component to manage content blocks.
 *
 * Content blocks are a special input, that are not managed in the table of the table itself.
 * The data resides in the database table 'cms_content_blocks'. This is a database table has a relationships
 * that connect to other tables.
 *
 * This component will recursively query the other tables and set the data to this component.
 */
class ComponentContentBlocks extends AbstractComponent
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

    /**
     * Loop through the content blocks and set the children as the current value
     */
    public function setValueFromStorage(mixed $value): bool
    {
        $contentBlocks = $this->getConnectedContentBlocks();

        $contentBlocksWithValues = new Collection;
        foreach ($contentBlocks as $contentBlock) {
            /** @var CmsContentBlocks $contentBlock */
            $table = Table::whereId($contentBlock->target_table_id)->first();
            $ts = new TableService($table, $this->assetService->getLang(), $contentBlock->target_record_id);

            $fieldComponents = $ts->getComponents();
            $contentBlocksWithValues->add($fieldComponents);
        }

        $this->currentValue = $contentBlocksWithValues;

        return true;
    }

    public function setNewValue(mixed $value): void
    {
        if (! is_array($value)) {
            $this->newValue = [];

            return;
        }

        $this->newValue = $value;
    }

    public function getDisplayValue(): array
    {
        $contentBlocks = $this->getConnectedContentBlocks();

        $contentBlocksWithValues = [];
        foreach ($contentBlocks as $contentBlock) {
            /** @var CmsContentBlocks $contentBlock */
            $table = Table::whereId($contentBlock->target_table_id)->first();

            $ts = new TableService($table, $this->assetService->getLang(), $contentBlock->target_record_id);
            $fieldComponents = $ts->getComponents();

            $tableValues = new \stdClass;
            foreach ($fieldComponents as $fieldComponent) {
                $tableValues->{$fieldComponent->assetItem->internal} = $fieldComponent->getDisplayValue();
            }
            $tableValues->id = $contentBlock->target_record_id;
            $contentBlocksWithValues[] = (object) [
                'type' => $fieldComponent->assetItem->table->url,
                'values' => $tableValues,
            ];
        }

        return $contentBlocksWithValues;
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

            // Recursively update the table that is set inside this component
            $ts->validate(new Request($block['items']));
            if ($block['recordId'] === null) {
                $recordId = $ts->create();
            } else {
                $recordId = $ts->update();
            }

            if ($block['recordId'] === null) {
                $cc = new CmsContentBlocks([
                    'asset_type' => $this->assetType,
                    'source_asset_item_id' => $this->assetItem->id,
                    'source_record_id' => $this->getRecordId(),
                    'target_table_id' => $block['tableId'],
                    'target_record_id' => $recordId,
                    'lang_id' => $this->assetService->getLang()->id,
                    'order' => $block['order'] ?? 1,
                ]);

                $cc->save();
            } elseif ($block['deleted'] === true) {
                $cc = CmsContentBlocks::where([
                    'asset_type' => $this->assetType,
                    'source_asset_item_id' => $this->assetItem->id,
                    'source_record_id' => $this->getRecordId(),
                    'target_table_id' => $block['tableId'],
                    'target_record_id' => $block['recordId'],
                    'lang_id' => $this->assetService->getLang()->id,
                ]);

                $cc->delete();
            } else {
                // Always update the order
                // TODO: performance improvement: Can probably be checked if it changed
                $cc = CmsContentBlocks::where([
                    'asset_type' => $this->assetType,
                    'source_asset_item_id' => $this->assetItem->id,
                    'source_record_id' => $this->getRecordId(),
                    'target_table_id' => $block['tableId'],
                    'target_record_id' => $block['recordId'],
                    'lang_id' => $this->assetService->getLang()->id,
                ]);

                $cc->update(['order' => $block['order']]);
            }
        }
    }

    public function getConnectedContentBlocks(): Collection
    {
        $assetItemId = $this->assetItem->id;
        $lang = $this->assetService->getLang();

        $contentBlocks = CmsContentBlocks::whereAssetType($this->assetType)
            ->whereSourceAssetItemId($assetItemId)
            ->whereSourceRecordId($this->getRecordId())
            ->where('lang_id', $lang->id)
            ->orderBy('order', 'ASC')
            ->get();

        return $contentBlocks;
    }

    /**
     * Content blocks are not stored in the record itself, so there is no value to clone.
     */
    public function getCloneValue(Collection $components): mixed
    {
        return null;
    }

    /**
     * Content blocks live in cms_content_blocks and point to records in other tables.
     * So we duplicate every connected record and link the copy to the new record.
     *
     * Because the duplication runs through the TableService, content blocks inside the
     * duplicated blocks are cloned as well.
     */
    public function clone(int $newRecordId): bool
    {
        $contentBlocks = CmsContentBlocks::whereAssetType($this->assetType)
            ->whereSourceAssetItemId($this->assetItem->id)
            ->whereSourceRecordId($this->getRecordId())
            ->orderBy('order', 'ASC')
            ->get();

        foreach ($contentBlocks as $contentBlock) {
            /** @var CmsContentBlocks $contentBlock */
            $table = Table::whereId($contentBlock->target_table_id)->first();
            $lang = Lang::find($contentBlock->lang_id);

            if ($table === null || $lang === null) {
                Log::withContext(['contentBlockId' => $contentBlock->id])
                    ->warning('[ContentBlock] Could not clone block, table or language not found');

                continue;
            }

            $ts = new TableService($table, $lang, $contentBlock->target_record_id);
            $newTargetRecordId = $ts->duplicateRecord();

            if ($newTargetRecordId === null) {
                Log::withContext(['contentBlockId' => $contentBlock->id])
                    ->warning('[ContentBlock] Could not clone block, duplicating the record failed');

                continue;
            }

            CmsContentBlocks::create([
                'asset_type' => $contentBlock->asset_type,
                'source_asset_item_id' => $contentBlock->source_asset_item_id,
                'source_record_id' => $newRecordId,
                'target_table_id' => $contentBlock->target_table_id,
                'target_record_id' => $newTargetRecordId,
                'lang_id' => $contentBlock->lang_id,
                'order' => $contentBlock->order,
            ]);
        }

        return true;
    }

    /**
     * The blocks of every language are purged, including the records they point
     * to. Those records are purged through the TableService, so the blocks
     * inside them are removed as well.
     */
    public function purge(): bool
    {
        $contentBlocks = CmsContentBlocks::withTrashed()
            ->whereAssetType($this->assetType)
            ->whereSourceAssetItemId($this->assetItem->id)
            ->whereSourceRecordId($this->getRecordId())
            ->get();

        $succeeded = true;
        foreach ($contentBlocks as $contentBlock) {
            /** @var CmsContentBlocks $contentBlock */
            $table = Table::whereId($contentBlock->target_table_id)->first();
            $lang = Lang::find($contentBlock->lang_id);

            if ($table === null || $lang === null) {
                Log::withContext(['contentBlockId' => $contentBlock->id])
                    ->warning('[ContentBlock] Could not purge block, table or language not found');

                $succeeded = false;

                continue;
            }

            $ts = new TableService($table, $lang, $contentBlock->target_record_id);
            if (! $ts->purge()) {
                $succeeded = false;
            }

            $contentBlock->forceDelete();
        }

        return $succeeded;
    }
}
