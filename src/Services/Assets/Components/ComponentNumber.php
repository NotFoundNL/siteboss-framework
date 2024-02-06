<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputNumber;

class ComponentNumber extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputNumber($this->assetItem->internal, $this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }
}
