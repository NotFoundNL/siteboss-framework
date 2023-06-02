<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\LayoutTitle;

class ComponentHeader extends AbstractComponent
{
    protected bool $useDefaultStorageMechanism = false;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutTitle($this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        return true;
    }
}
