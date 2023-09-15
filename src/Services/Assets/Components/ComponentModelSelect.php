<?php

namespace NotFound\Framework\Services\Assets\Components;

use Illuminate\Support\Facades\DB;
use NotFound\Framework\Models\Lang;
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
                $item->{$server_properties->foreignKey},
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
        $table = $this->removeDatabasePrefix($this->properties()->foreignTable);

        $properties = $this->properties();
        if (isset($properties->localizeForeign) && $properties->localizeForeign == true) {
            $value = DB::table($table)//
                ->join($table.'_tr', $table.'_tr.entity_id', '=', $table.'.id')
                ->where($table.'_tr.lang_id', Lang::current()->id)
                ->where($table.'.id', $this->getCurrentValue())
                ->value($properties->foreignDisplay);
        } else {
            $value = DB::table($table)->whereId($this->getCurrentValue())->value($this->properties()->foreignDisplay);
        }

        return new LayoutTableColumn($value ?? '-', $this->type);
    }

    private function getNormalTableData()
    {
        $modelNameSpace = $this->properties()->name_space ?? 'NotFound\\ELearning\\Models\\';

        $model = $modelNameSpace.$this->removeDatabasePrefix($this->properties()->foreignTable);

        $allModels = $model::all();

        return $allModels;
    }

    /*
     * In the properties of the tableitem the custom query is defined in the
     * database. This custom query already has the database prefix set.LoketTableSelect
     */
    private function getCustomQueryData(): array
    {
        return DB::select(DB::raw($this->properties()->customQuery));
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
