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

            $item = (object) [];
            if (config('siteboss.export_retain_ids')) {
                $item->id = $tableItem->id;
            }

            $item->rights = $tableItem->rights;
            $item->internal = $tableItem->internal;
            $item->type = $tableItem->type;
            $item->name = $tableItem->name;
            $item->description = $tableItem->description;
            $item->properties = $tableItem->properties ?? (object) [];
            $item->enabled = $tableItem->enabled === 1 ?? false;
            $item->global = $tableItem->global === 1 ?? false;
            $item->server_properties =
                $tableItem->server_properties ?? (object) [];

            $items[] = $item;
        }

        $exportItem = (object) [];

        if (config('siteboss.export_retain_ids')) {
            $exportItem->id = $this->id;
        }
        $exportItem->siteboss_asset = (object) [
            'version' => '1.2.0',
            'type' => 'table',
        ];
        $exportItem->model = $this->model;
        $exportItem->comments = $this->comments;
        $exportItem->rights = $this->rights;
        $exportItem->url = $this->url;
        $exportItem->name = $this->name;
        $exportItem->allow_create = $this->allow_create;
        $exportItem->allow_delete = $this->allow_delete;
        $exportItem->allow_sort = $this->allow_sort;
        $exportItem->properties = $this->properties;
        $exportItem->enabled = $this->enabled === 1 ?? false;
        $exportItem->items = $items;

        return $exportItem;
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
