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

        $server_properties = $this->properties();

        $items = $this->getNormalTableData();

        foreach ($items as $item) {
            $inputDropdown->addItem(
                $item->id,
                $item->{$server_properties->foreignDisplay}()
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
        $value = $this->properties()->selectedModel::find($this->getCurrentValue()) ?? null;

        return new LayoutTableColumn($value->{$this->properties()->foreignDisplay}() ?? '-', $this->type);
    }

    private function getNormalTableData()
    {
        return $this->properties()->selectedModel::all();
    }

    /**
     * Get the value used in the default storage mechanism.
     * This is always a string. Use JSON or your own logic for other types of values.
     *
     * @return string
     */
    public function getValueForStorage(): ?string
    {
        return $this->newValue === '' ? null : $this->newValue;
    }
}
