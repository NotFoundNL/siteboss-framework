<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Framework\Services\Legacy\StatusColumn;
use Illuminate\Support\Facades\DB;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputDropdown;

class ComponentDropDown extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $inputDropdown = new LayoutInputDropdown($this->assetItem->internal, $this->assetItem->name);

        $properties = $this->properties();

        foreach ($properties->items as $item) {
            $inputDropdown->addItem($item->value, $item->label);
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
        $value = '-';

        $properties = $this->properties();

        foreach ($properties->items as $item) {
            if ($item->value == $this->getCurrentValue()) {
                $value = $item->label;
                break;
            }
        }

        return new LayoutTableColumn($value ?? '-', $this->type);
    }

    private function getNormalTableData($properties): array
    {
        $tableName = remove_database_prefix($this->properties()->foreignTable);

        $properties = $this->properties();
        $builder = DB::table($tableName);
        if (isset($properties->useStatus) && $properties->useStatus == true) {
            $builder = StatusColumn::wherePublished(DB::table($tableName), $tableName);
        }

        return $builder->get()->toArray();
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
