<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Support\Collection;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Template;
use NotFound\Framework\Models\TemplateItem;
use NotFound\Framework\Services\Assets\Components\FactoryComponent;
use NotFound\Framework\Services\Assets\Enums\AssetType;

class GlobalPageService extends AbstractAssetService
{
    protected Collection $fieldComponents;

    protected bool $valuesSet = false;

    public function __construct()
    {
        $this->assetModel = new Template(['id' => 0]);
        $this->lang = Lang::current();
        $this->fieldComponents = $this->getFieldComponents(0);
    }

    public function getType(): AssetType
    {
        return AssetType::PAGE;
    }

     public function getComponents(): Collection
     {
         return $this->fieldComponents;
     }

     protected function getCacheKey(): string
     {
         return $this->lang->url.'page_globals';
     }

    /**
     * Loops through all the table items and return them with the appropriate Input Class
     */
    public function getFieldComponents(?int $recordId = null): Collection
    {
        $templateItems = TemplateItem::where('enabled', 1)
            ->where('global', 1)
            ->with('string')
            ->get();

        $factory = new FactoryComponent($this);
        $inputs = new Collection;
        foreach ($templateItems as $item) {
            $newComponent = $factory->getByType($item);
            $newComponent->setRecordId($recordId);
            $newComponent->setValueFromStorage($item->string?->value ?? '');

            $inputs->put($item->internal, $newComponent);
        }

        return $inputs;
    }
}
