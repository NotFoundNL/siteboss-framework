<?php

namespace NotFound\Framework\Traits;

use Exception;
use Sb;

trait Exportable
{
    public function exportToObject(): object
    {
        $tableItems = $this->items()->orderBy('order', 'asc')->get();
        $items = [];

        foreach ($tableItems as $tableItem) {
            $items[] = (object) [
                'id' => $tableItem->id,
                'rights' => $tableItem->rights,
                'internal' => $tableItem->internal,
                'type' => $tableItem->type,
                'name' => $tableItem->name,
                'description' => $tableItem->description,
                'properties' => $tableItem->properties,
                'order' => $tableItem->order,
                'enabled' => $tableItem->enabled,
                'global' => $tableItem->global ?? 0,
                'server_properties' => $tableItem->server_properties,
            ];
        }

        return (object) [
            'id' => $this->id,
            'comments' => $this->comments,
            'rights' => $this->rights,
            'url' => $this->url,
            'table' => $this->getSiteTableName(),
            'name' => $this->name,
            'allow_create' => $this->allow_create,
            'allow_delete' => $this->allow_delete,
            'allow_sort' => $this->allow_sort,
            'properties' => $this->properties,
            'enabled' => $this->enabled,
            'items' => $items,
        ];
    }

    public function exportToFile(): bool
    {
        $exportData = $this->exportToObject();
        $tableConfigFile = base_path('resources/siteboss/tables/'.$this->getSiteTableName().'.json');
        if (! file_exists($tableConfigFile)) {
            Sb::makeDirectory(base_path(), 'resources/siteboss/tables/');
        }

        try {
            file_put_contents($tableConfigFile, json_encode($exportData, JSON_PRETTY_PRINT));

            return true;
        } catch (Exception) {
            throw new \Exception('Could not write '.$this->table.' JSON file');
        }

        return false;
    }

    public function importFromFile()
    {

    }
}
