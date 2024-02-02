<?php

namespace NotFound\Framework\Traits;

use Exception;
use NotFound\Framework\Services\CmsExchange\TableExchangeService;
use NotFound\Framework\Services\CmsExchange\TemplateExchangeService;
use Sb;

trait Exchangeable
{
    private TableExchangeService|TemplateExchangeService $exchangeService;

    public function exportToObject(): object
    {
        $class = explode('\\', get_class($this));
        $classType = strtolower(end($class));

        if ($classType === 'table') {
            $this->exchangeService = new TableExchangeService($this);
        } else {
            $this->exchangeService = new TemplateExchangeService($this);
        }

        $retainIds = $this->exchangeService->exportRetainIds();
        $typeName = $this->exchangeService->exportTypeName();

        $assetItems = $this->items()->orderBy('order', 'asc')->get();
        $items = [];

        foreach ($assetItems as $assetItem) {

            $item = (object) [];
            if ($retainIds) {
                $item->id = $assetItem->id;
            }

            $item->rights = $assetItem->rights;
            $item->internal = $assetItem->internal;
            $item->type = $assetItem->type;
            $item->name = $assetItem->name;
            $item->description = $assetItem->description;
            $item->properties = $assetItem->properties ?? (object) [];
            $item->enabled = $assetItem->enabled === 1 ?? false;
            $item->global = $assetItem->global === 1 ?? false;
            $item->server_properties =
                $assetItem->server_properties ?? (object) [];

            $items[] = $item;
        }

        $exportItem = (object) [];

        if ($retainIds) {
            $exportItem->id = $this->id;
        }
        $exportItem->siteboss_asset = (object) [
            'version' => '1.3.0',
            'type' => $typeName,
        ];
        $exportItem->model = $this->model;
        $exportItem->comments = $this->comments;
        $exportItem->rights = $this->rights;
        $exportItem->url = $this->url;
        $exportItem->name = $this->name;

        if ($classType === 'table') {
            $exportItem->allow_create = $this->allow_create;
            $exportItem->allow_delete = $this->allow_delete;
            $exportItem->allow_sort = $this->allow_sort;
        } else {
            $exportItem->allow_children = $this->allow_children;
            $exportItem->filename = $this->filename;
            $exportItem->params = $this->params;
            $exportItem->desc = $this->desc;
        }

        $exportItem->properties = $this->properties;
        $exportItem->enabled = $this->enabled;
        $exportItem->items = $items;

        return $exportItem;
    }

    public function exportToFile(): bool
    {
        $exportData = $this->exportToObject();
        // exchangeService is set in exportToObject
        $path = 'resources/siteboss/'.$this->exchangeService->exportTypeNamePlural().'/';
        $tableConfigFile = base_path($path.$this->getSiteTableName().'.json');
        if (! file_exists($tableConfigFile)) {
            Sb::makeDirectory(base_path(), $path);
        }

        try {
            file_put_contents($tableConfigFile, json_encode($exportData, JSON_PRETTY_PRINT));

            return true;
        } catch (Exception) {
            throw new \Exception('Could not write '.$this->table.' JSON file');
        }

        return false;
    }
}
