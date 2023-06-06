<?php

namespace NotFound\Framework\Http\Controllers\Assets;

use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableItem;
use NotFound\Framework\Services\Assets\Components\AbstractComponent;
use NotFound\Framework\Services\Assets\Components\FactoryComponent;
use NotFound\Framework\Services\Assets\TableService;

class TableItemEditorController extends Controller
{
    public function ajaxGet(Table $table, $recordId, $fieldInternal, $langUrl)
    {
        $component = $this->getComponent($table, $recordId, $fieldInternal, $langUrl);

        return $component->asyncGetRequest();
    }

    public function ajaxPut(Table $table, $recordId, $fieldInternal, $langUrl)
    {
        $component = $this->getComponent($table, $recordId, $fieldInternal, $langUrl);

        return $component->asyncPutRequest();
    }

    public function ajaxPost(Table $table, $recordId, $fieldInternal, $langUrl)
    {
        $component = $this->getComponent($table, $recordId, $fieldInternal, $langUrl);

        return $component->asyncPostRequest();
    }

    private function getComponent(Table $table, $recordId, $langSlug, $fieldInternal): AbstractComponent
    {
        $tableItem = TableItem::whereInternal($fieldInternal)->whereTableId($table->id)->firstOrFail();
        $lang = Lang::whereUrl($langSlug)->firstOrFail();

        $factory = new FactoryComponent(new TableService($table, $lang, $recordId));
        $component = $factory->getByType($tableItem);
        if (! $component) {
            abort(404, 'No component');
        }
        $component->setRecordId($recordId);

        return $component;
    }
}
