<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Facades\Log;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputDropdown;

class ComponentCustomSelect extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $inputDropDown = new LayoutInputDropdown($this->assetItem->internal, $this->assetItem->name);

        $properties = $this->assetItem->properties;
        if (isset($this->properties()->service) && class_exists($this->properties()->service)) {
            $prop = $this->properties();
            $class = new $prop->service($this->assetModel, $this->assetItem, $this->getCurrentValue());
            foreach ($class->getItemsForSelect() as $item) {
                $inputDropDown->addItem($item->{$properties->foreignkey}, $item->{$properties->foreigndisplay});
            }
        } else {
            Log::withContext(['id' => $this->assetItem->id])->warning('Property service not set for ComponentCustomSelect');
        }

        return $inputDropDown;
    }

    public function getTableOverviewContent(): LayoutTableColumn
    {
        $column = new LayoutTableColumn('', $this->type);
        if (class_exists($this->properties()->service)) {
            $properties = $this->properties();
            $class = new $properties->service($this->assetModel, $this->assetItem, $this->getCurrentValue());

            $column->value = $class->getTableOverviewContent();
        }

        return $column;
    }

    public function validate($newValue): bool
    {
        // TODO: Implement validate() method.
        return true;
    }
}
