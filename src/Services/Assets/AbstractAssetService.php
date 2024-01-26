<?php

namespace NotFound\Framework\Services\Assets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use NotFound\Framework\Models\AssetModel;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Services\Assets\Components\FactoryComponent;
use NotFound\Framework\Services\Assets\Enums\AssetType;

abstract class AbstractAssetService
{
    protected AssetModel $assetModel;

    protected array $requestParameters;

    protected Lang $lang;

    /**
     * Get the type of the asset
     */
    abstract public function getType(): AssetType;

    abstract public function getComponents(): Collection;

    abstract protected function getCacheKey(): string;

    public function setRequestParameters(array|null $requestParameters): void
    {
        $this->requestParameters = $requestParameters;
    }

    public function getRequestParameters($key = null): string|array|null
    {
        if(!isset($this->requestParameters)) {
            return null;
        }

        if(isset($key))
        {
            return (key_exists($key, $this->requestParameters)) ? $this->requestParameters[$key] : [];
        }

        return $this->requestParameters;
    }

    public function getAssetModel(): ?AssetModel
    {
        return $this->assetModel ?? null;
    }

    public function getLang(): Lang
    {
        return $this->lang;
    }

    /**
     * Loops through all the table items and return them with the appropriate Input Class
     */
    public function getFieldComponents(?int $recordId = null): Collection
    {
        $items = $this->assetModel->items()->where('enabled', 1)->orderBy('order')->get();

        $factory = new FactoryComponent($this);
        $inputs = new Collection();
        foreach ($items as $assetItem) {
            $newInput = $factory->getByType(
                assetItem: $assetItem
            );

            $newInput->setRecordId($recordId);

            $inputs->put($assetItem->internal, $newInput);
        }

        return $inputs;
    }

    /**
     * Loops through all the table items and return them with the appropriate Input Class
     * Checks if the overview variable in property is set to true
     */
    public function getFieldComponentsOverview(): Collection
    {
        $items = $this->assetModel->items()->where('enabled', 1)->orderBy('order')->get();

        $factory = new FactoryComponent($this);
        $inputs = new Collection();
        foreach ($items as $assetItem) {
            if (! $assetItem->isInOverview()) {
                continue;
            }

            $newInput = $factory->getByType(assetItem: $assetItem);

            $inputs->put($assetItem->internal, $newInput);
        }

        return $inputs;
    }

    public function getCachedValues(): array
    {
        // This is not the proper place for caching as it wil require you to create
        // an AssetService for every cached value.

        trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);

        return Cache::remember($this->getCacheKey(), 3600, function () {
            return $this->getValues();
        });
    }

    public function getValues(): array
    {
        $array = [];

        foreach ($this->getComponents() as $component) {
            /** @var AbstractComponent $component */
            $array[$component->assetItem->internal] = (object) [
                'type' => $component->getFieldType(),
                'properties' => $component->properties(),
                'val' => $component->getDisplayValue(),
            ];
        }

        return $array;
    }
}
