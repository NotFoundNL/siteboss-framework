<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputDropdown;

class ComponentModelSelect extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $inputDropdown = new LayoutInputDropdown($this->assetItem->internal, $this->assetItem->name);

        $items = $this->getModels();

        foreach ($items as $key => $value) {
            $inputDropdown->addItem(
                $key,
                $value,
            );
        }

        return $inputDropdown;
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        $value = $this->properties()->selectedModel::{$this->properties()->methodName}($this->getCurrentValue(), true);

        $display = ($value) ? $value : '-';

        return new LayoutTableColumn($display, $this->type);
    }

    private function getModels()
    {
        return $this->properties()->selectedModel::{$this->properties()->methodName}($this->getCurrentValue());
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     */
    public function getValueForStorage(): ?string
    {
        return $this->newValue === '' ? null : $this->newValue;
    }
}
