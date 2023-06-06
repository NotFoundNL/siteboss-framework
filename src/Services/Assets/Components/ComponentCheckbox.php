<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputCheckbox;

class ComponentCheckbox extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return new LayoutInputCheckbox($this->assetItem->internal, $this->assetItem->name);
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        // TODO: Make column clickable if necessary
        return new LayoutTableColumn($this->getCurrentValue(), $this->type, (object) ['recordId' => 'id']);
    }

    public function getCurrentValue()
    {
        return is_bool($this->currentValue) ? $this->currentValue : $this->currentValue == 1;
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     *
     * @return string
     */
    public function getValueForStorage(): ?string
    {
        return $this->newValue ? '1' : '0';
    }
}
