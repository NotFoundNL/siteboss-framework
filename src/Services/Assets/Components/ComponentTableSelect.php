<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Framework\Services\Legacy\StatusColumn;
use Illuminate\Support\Facades\DB;
use NotFound\Layout\Elements\AbstractLayout;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Inputs\LayoutInputDropdown;

class ComponentTableSelect extends AbstractComponent
{
    public function getAutoLayoutClass(): ?AbstractLayout
    {
        $inputDropdown = new LayoutInputDropdown($this->assetItem->internal, $this->assetItem->name);

        $server_properties = $this->properties();

        if (isset($server_properties->searchForItem) && $server_properties->searchForItem == true) {
            $inputDropdown->setSearchable();
        }

        if (isset($server_properties->customQuery) && $server_properties->customQuery != '') {
            $items = $this->getCustomQueryData();
        } else {
            $items = $this->getNormalTableData();
        }

        foreach ($items as $item) {
            $inputDropdown->addItem(
                $item->{$server_properties->foreignKey},
                $item->{$server_properties->foreignDisplay}
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
        $tableName = remove_database_prefix($this->properties()->foreignTable);
        $value = DB::table($tableName)->whereId($this->getCurrentValue())->value($this->properties()->foreignDisplay);

        return new LayoutTableColumn($value ?? '-', $this->type);
    }

    private function getNormalTableData(): array
    {
        $properties = $this->properties();
        $tableName = remove_database_prefix($properties->foreignTable);

        $builder = DB::table($tableName);
        if (isset($properties->useStatus) && $properties->useStatus == true) {
            $builder = StatusColumn::wherePublished(DB::table($tableName), $tableName);
        }

        if (isset($properties->useOrder) && $properties->useOrder == true) {
            $builder->orderBy('order', 'asc');
        } else {
            $builder->orderBy($properties->foreignDisplay, 'asc');
        }

        return $builder->get()->toArray();
    }

    /*
     * In the properties of the tableitem the custom query is defined in the
     * database. This custom query already has the database prefix set.LoketTableSelect
     */
    private function getCustomQueryData(): array
    {
        return Db::select(DB::raw($this->properties()->customQuery));
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
