<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\AssetModel;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\Template;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Inputs\LayoutInputTextArea;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;
use Sb;

class CmsEditorImportExportController extends Controller
{
    public function getImport($table_id, $type)
    {
        $importWidget = new LayoutWidget('Import', 1);
        $importForm = new LayoutForm('/app/editor/'.$type.'/'.$table_id.'/import');
        $importForm->addInput(new LayoutInputTextArea('import'));
        $importForm->addButton(new LayoutButton('Import'));
        $importWidget->addForm($importForm);

        return $importWidget;
    }

    public function getExport($tables)
    {
        $exportData = CmsEditorImportExportController::getTableItemExport($tables);

        $exportWidget = new LayoutWidget('Export', 1);
        $exportForm = new LayoutForm('');
        $exportForm->addText(new LayoutText(json_encode($exportData)));
        $exportWidget->addForm($exportForm);

        return $exportWidget;
    }

    public function exportAll()
    {
        $response = new LayoutResponse();
        $tables = Table::all();

        foreach ($tables as $table) {
            // TODO: catch exceptions
            $table->exportToFile();
        }

        $response->addAction(new Toast($tables->count().' tables exported successfully'));

        return $response->build();
    }

    public function getTableItemExport($tables)
    {
        $exportData = [];

        foreach ($tables as $tableItem) {
            $exportData[] = (object) [
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

        return $exportData;
    }

    private function tableToFile(Table $table)
    {
        $tableItems = $table->items()->orderBy('order', 'asc')->get();
        $exportData[] = (object) [
            'id' => $table->id,
            'comments' => $table->comments,
            'rights' => $table->rights,
            'url' => $table->url,
            'table' => $table->table,
            'name' => $table->name,
            'allow_create' => $table->allow_create,
            'allow_delete' => $table->alllow_delete,
            'allow_sort' => $table->allow_sort,
            'properties' => $table->properties,
            'enabled' => $table->enabled,
            'items' => CmsEditorImportExportController::getTableItemExport($tableItems),
        ];

        $tableConfigFile = base_path('resources/siteboss/tables/'.$table->table.'.json');
        if (! file_exists($tableConfigFile)) {
            Sb::makeDirectory(base_path(), 'resources/siteboss/');
        }

        try {
            file_put_contents($tableConfigFile, json_encode($exportData, JSON_PRETTY_PRINT));

            return true;
        } catch (Exception) {
            throw new \Exception('Could not write '.$table->table.' JSON file');
        }

        return false;
    }

    public function importTemplate(FormDataRequest $request, Template $table)
    {
        return $this->import($request, $table);
    }

    public function importTable(FormDataRequest $request, Table $table)
    {
        return $this->import($request, $table);
    }

    private function import(FormDataRequest $request, AssetModel $table)
    {
        $response = new LayoutResponse();
        $data = json_decode($request->import);
        if (! $data || $data == '') {
            $response->addAction(new Toast('Foutieve JSON data', 'error'));

            return $response->build();
        }
        $max = $table->items()->max('order');

        try {
            foreach ($data as $tableItem) {
                $table->items()->create(
                    [
                        'rights' => $tableItem->rights,
                        'internal' => $tableItem->internal,
                        'type' => $tableItem->type,
                        'name' => $tableItem->name,
                        'description' => $tableItem->description,
                        'properties' => $tableItem->properties,
                        'order' => ++$max,
                        'global' => $tableItem->global ?? 0,
                        'enabled' => $tableItem->enabled,
                        'server_properties' => $tableItem->server_properties,
                    ]
                );
            }
            $response->addAction(new Toast('Succesvol geimporteerd (Refresh om de wijzigingen te zien)'));
        } catch (\Exception $e) {
            $response->addAction(new Toast('Error importing. '.$e->getMessage(), 'error'));
        }

        return $response->build();
    }
}
