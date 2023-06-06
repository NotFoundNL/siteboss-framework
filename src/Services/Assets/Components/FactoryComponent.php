<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Framework\Models\AssetItem;
use NotFound\Framework\Services\Assets\AbstractAssetService;

class FactoryComponent
{
    private $classNamespace = '\\NotFound\\Framework\\Services\\Assets\\Components\\';

    private $classPrefix = 'Component';

    public function __construct(
        protected AbstractAssetService $assetService
    ) {
    }

    public function getByType(AssetItem $assetItem): AbstractComponent
    {
        $className = $this->classNamespace.$this->classPrefix.ucfirst($assetItem->type);

        if (class_exists($className) === false) {
            $className = $this->classNamespace.$this->classPrefix.'Default';
        }

        return new $className($this->assetService, $assetItem);
    }
}
