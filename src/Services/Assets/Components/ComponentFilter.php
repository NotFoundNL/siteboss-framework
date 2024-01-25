<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Inputs\LayoutInputHidden;

class ComponentFilter extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputHidden($this->assetItem->internal, $this->assetItem->name);
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function beforeSave()
    {
        if ($this->getCurrentValue()) {
            $this->setNewValue($this->getCurrentValue());

            return;
        }

        //TODO get data from somewhere else
        if (isset(request()->query('filter')[$this->assetItem->internal])) {
            $this->setNewValue(request()->query('filter')[$this->assetItem->internal]);
        }
    }
}
