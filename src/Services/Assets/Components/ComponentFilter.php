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

        $filter = $this->assetService->getRequestParameters('filter');

        if (! isset($filter)) {
            return;
        }

        if (array_key_exists($this->assetItem->internal, $filter)) {
            $this->setNewValue($filter[$this->assetItem->internal]);
        }
    }
}
