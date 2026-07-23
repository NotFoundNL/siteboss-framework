<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\LayoutText;

class ComponentDescription extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutText($this->assetItem->properties->desc);
    }

    public function validate($newValue): bool
    {
        return true;
    }

    public function purge(): bool
    {
        // Only shows a description in the form, there is nothing to remove.
        return true;
    }
}
