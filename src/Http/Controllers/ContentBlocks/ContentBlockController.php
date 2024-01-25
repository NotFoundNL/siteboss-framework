<?php

namespace NotFound\Framework\Http\Controllers\ContentBlocks;

use Illuminate\Support\Collection;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Services\Assets\Components\AbstractComponent;
use NotFound\Framework\Services\Assets\TableService;

class ContentBlockController
{
    /**
     * Get contentBlock template items.
     *
     * @param  $csvTables  cms_tables->table in csv format
     */
    public function get(string $csvTables)
    {
        $tableArray = explode(',', $csvTables);
        $tables = new Collection();
        foreach ($tableArray as $tableName) {
            $tableModel = Table::where('table', $tableName)->first();

            if ($tableModel) {
                $tables->add($tableModel);
            }
        }

        $newTables = $tables->map(function ($table) {
            $inputs = (new TableService($table, lang::default()))->getFieldComponents();

            $newInputs = [];
            foreach ($inputs as $component) {
                /** @var AbstractComponent $component */
                $component->setValueFromStorage('');
                $newInputs[] = $component->buildAutoLayoutClass();
            }
            unset($table->items);
            $table->items = $newInputs;

            return $table;
        });

        return $newTables;
    }
}
