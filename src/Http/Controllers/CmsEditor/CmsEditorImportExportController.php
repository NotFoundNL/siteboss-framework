<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\AssetModel;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\Template;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;

class CmsEditorImportExportController extends Controller
{
    public function exportAllTables()
    {
        $response = new LayoutResponse();
        $tables = Table::all();

        foreach ($tables as $table) {
            // TODO: catch exceptions
            $table->exportToFile();
        }

        $response->addAction(
            new Toast($tables->count().' tables exported successfully')
        );

        return $response->build();
    }

    // public function importTemplate(FormDataRequest $request, Template $table)
    // {
    //     return $this->import($request, $table);
    // }

    // public function importTable(FormDataRequest $request, Table $table)
    // {
    //     return $this->import($request, $table);
    // }

    // private function import(FormDataRequest $request, AssetModel $table)
    // {
    //     $response = new LayoutResponse();
    //     $tableItem = json_decode($request->import);
    //     if (! $data || $data == '') {
    //         $response->addAction(new Toast('Foutieve JSON data', 'error'));

    //         return $response->build();
    //     }
    //     $max = $table->items()->max('order');

    //     try {
    //         foreach ($data->items as $tableItem) {
    //             $table->items()->create(
    //                 [
    //                     'rights' => $tableItem->rights,
    //                     'internal' => $tableItem->internal,
    //                     'type' => $tableItem->type,
    //                     'name' => $tableItem->name,
    //                     'description' => $tableItem->description,
    //                     'properties' => $tableItem->properties,
    //                     'order' => ++$max,
    //                     'global' => $tableItem->global ?? 0,
    //                     'enabled' => $tableItem->enabled,
    //                     'server_properties' => $tableItem->server_properties,
    //                 ]
    //             );
    //         }
    //         $response->addAction(new Toast('Succesvol geimporteerd (Refresh om de wijzigingen te zien)'));
    //     } catch (\Exception $e) {
    //         $response->addAction(new Toast('Error importing. '.$e->getMessage(), 'error'));
    //     }

    //     return $response->build();
    // }
}
