<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\AssetModel;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Inputs\LayoutInputTextArea;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class CmsEditorImportExportController extends \NotFound\Framework\Http\Controllers\Controller
{
    public static function getImport($table_id, $type)
    {
        $importWidget = new LayoutWidget('Import', 1);
        $importForm = new LayoutForm('/app/editor/'.$type.'/'.$table_id.'/import');
        $importForm->addInput(new LayoutInputTextArea('import'));
        $importForm->addButton(new LayoutButton('Import'));
        $importWidget->addForm($importForm);

        return $importWidget;
    }

    public static function getExport($tables)
    {
        $exportData = [];

        foreach ($tables as $tableItem) {
            $exportData[] = (object) [
                'rights' => $tableItem->rights,
                'internal' => $tableItem->internal,
                'type' => $tableItem->type,
                'name' => $tableItem->name,
                'description' => $tableItem->description,
                'properties' => $tableItem->properties,
                'enabled' => $tableItem->enabled,
                'global' => $tableItem->global,
                'server_properties' => $tableItem->server_properties,
            ];
        }

        $exportWidget = new LayoutWidget('Export', 1);
        $exportForm = new LayoutForm('');
        $exportForm->addText(new LayoutText(json_encode($exportData)));
        $exportWidget->addForm($exportForm);

        return $exportWidget;
    }

    public function import(FormDataRequest $request, AssetModel $table)
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
                        'enabled' => $tableItem->enabled,
                        'global' => $tableItem->global,
                        'server_properties' => $tableItem->server_properties ?? '{}',
                    ]
                );
            }
            $response->addAction(new Toast('Succesvol geimporteerd'));
            $response->addAction(new Redirect('/app/editor/table/'.$table->id));
        } catch (\Exception $e) {
            $response->addAction(new Toast('Fout bij uploaden. '.$e->getMessage(), 'error'));
        }

        return $response->build();
    }
}
