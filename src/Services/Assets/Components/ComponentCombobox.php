<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputDropdown;

class ComponentCombobox extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputDropdown($this->assetItem->internal, $this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }
}
