<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Facades\DB;
use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Framework\Helpers\Layout\Inputs\LayoutInputDropdown;
use NotFound\Framework\Services\Legacy\StatusColumn;

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
        $tableName = $this->removeDatabasePrefix($this->properties()->foreignTable);

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
     */
    public function getValueForStorage(): ?string
    {
        return $this->newValue === '' ? null : $this->newValue;
    }

    public function purge(): bool
    {
        // The value is stored in the record itself. The rows it points to are
        // shared with other records, so they are left alone.
        return true;
    }
}
