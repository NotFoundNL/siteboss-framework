<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputNumber;

class ComponentNumber extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $inputText = new LayoutInputNumber($this->assetItem->internal, $this->assetItem->name);

        return $inputText;
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function purge(): bool
    {
        // The value is stored in the record itself, there is nothing else to remove.
        return true;
    }
}
