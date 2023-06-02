<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\LayoutNotImplemented;

class ComponentDefault extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $item = new LayoutNotImplemented($this->assetItem->internal, $this->assetItem->name);

        return $item->setOriginalType($this->assetItem->type);
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }
}
