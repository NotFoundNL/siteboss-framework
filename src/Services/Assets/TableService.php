<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\Components\AbstractComponent;
use NotFound\Framework\Services\Assets\Enums\AssetType;

class TableService extends AbstractAssetService
{
    private Collection $fieldComponents;

    public function __construct(
        private Table $table,
        protected Lang $lang,
        private ?int $recordId = null,
    ) {
        $this->assetModel = $table;
        $this->fieldComponents = $this->getFieldComponents($this->recordId);

        if ($this->recordId !== null) {
            $this->setCurrentValues();
        }
    }

    public function getComponents(): Collection
    {
        return $this->fieldComponents;
    }

    /**
     * addCustomComponent
     *
     * Add a new component to the table
     * this is used by the ComponentChildTable class primarily
     *
     * @param  mixed  $internal
     * @param  mixed  $component
     */
    public function addCustomComponent(string $internal, AbstractComponent $component): void
    {
        $this->fieldComponents->put($internal, $component);
    }

    public function getType(): AssetType
    {
        return AssetType::TABLE;
    }

    /**
     * Loop over the active components and use the component to validate the the
     * value submitted.
     *
     * @param  mixed  $request
     * @return bool true if all components are valid
     */
    public function validate(Request $request): bool
    {
        foreach ($this->fieldComponents as $component) {
            /** @var AbstractComponent $component */
            $component->setNewValue($request->{$component->assetItem->internal});

            if ($component->isDisabled()) {
                continue;
            }

            if (! $component->validate($request->{$component->assetItem->internal})) {
                return false;
            }
        }

        return true;
    }

    public function create(): int
    {
        return $this->updateModel(new: true);
    }

    public function update(): int
    {
        return $this->updateModel();
    }

    public function delete(): void
    {
        $this->assetModel->deleteRecord($this->recordId); //, $langUrl);
    }

    protected function updateModel(bool $new = false): int
    {
        foreach ($this->fieldComponents as $component) {
            $component->beforeSave();
        }

        $id = $this->upsertNonLocalizedModel($new);
        if ($this->getAssetModel()->isLocalized()) {
            $this->upsertLocalizedModel($id);
        }

        foreach ($this->fieldComponents as $component) {
            $component->setRecordId($id);
        }

        foreach ($this->fieldComponents as $component) {
            $component->save();
        }

        foreach ($this->fieldComponents as $component) {
            $component->afterSave($this->lang);
        }

        return $id;
    }

    private function upsertNonLocalizedModel(bool $new): int
    {
        $record = [];
        foreach ($this->fieldComponents as $component) {
            /** @var AbstractComponent $component */
            if (
                ! $component->usesDefaultStorageMechanism()
                || $component->isDisabled()
            ) {
                continue;
            }

            if (! $this->assetModel->isLocalized() || ! $component->isLocalized()) {
                //TODO: move to componentfilter
                if($component->getFieldType() == 'Filter' && request()->query('filter'))
                {
                    $record[$component->assetItem->internal] = request()->query('filter')[$component->assetItem->internal];
                } else {
                    $record[$component->assetItem->internal] = $component->getValueForStorage();
                }
            }
        }

        if (! $new) {
            $record['id'] = $this->recordId;

            return $this->assetModel->updateRecord($record);
        }

        return $this->assetModel->createRecord($record);
    }

    private function upsertLocalizedModel(int $id): void
    {
        $localizedRecord = [];
        foreach ($this->fieldComponents as $component) {
            /** @var AbstractComponent $component */
            if (
                ! $component->usesDefaultStorageMechanism()
                || $component->isDisabled()
            ) {
                continue;
            }

            if ($component->isLocalized()) {
                $localizedRecord[$component->assetItem->internal] = $component->getValueForStorage();
            }
        }

        $this->assetModel->saveLocalize($localizedRecord, $id, $this->lang->id);
    }

    /**
     * Set current values for the components
     */
    private function setCurrentValues(): void
    {
        $siteTableRow = $this->assetModel->getSiteTableRowByRecordId($this->recordId);

        $localizedSiteRow = null;
        if ($this->table->isLocalized()) {
            $localizedSiteRow = $this->table->getLocalizedRow($siteTableRow->id, $this->lang);
        }

        $this->fieldComponents->transform(function ($component) use ($siteTableRow, $localizedSiteRow) {
            /** @var AbstractComponent $component */
            if ($component->isLocalized()) {
                $component->setValueFromStorage($localizedSiteRow->{$component->assetItem->internal} ?? '');
            } else {
                $component->setValueFromStorage($siteTableRow->{$component->assetItem->internal} ?? '');
            }

            return $component;
        });
    }

    protected function getCacheKey(): string
    {
        return 'table_'.$this->table->slug.'_'.$this->recordId.'_'.$this->lang->url;
    }
}
