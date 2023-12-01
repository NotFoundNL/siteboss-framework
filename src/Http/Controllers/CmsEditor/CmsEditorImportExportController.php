<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use File;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\AssetModel;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\Template;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
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

    public function import()
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('CMS Editor');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('CMS Import');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget('CMS Import', 12);

        $path = resource_path('siteboss/tables');
        if (! File::exists($path)) {
            $widget1->addText(new LayoutText('No export files found in '.$path));
        } else {

            $widget1->addText(new LayoutText('Currently these changes are available:'));

            $table = new LayoutTable(delete: false, edit: false, create: false, sort: false);

            $table->addHeader(new LayoutTableHeader('Resource', 'resource'));

            $table->addHeader(new LayoutTableHeader('Name', 'name'));

            $filenames = [];
            $files = File::files($path);
            foreach ($files as $file) {
                $index = str_replace('.json', '', $file->getFilename());
                $filenames[$index] = json_decode(file_get_contents($file->getPathname()));
            }

            $tables = Table::all()->sortBy('table');
            foreach ($tables as $cmsTable) {

                $row = new LayoutTableRow(1, '/app/editor/import/');
                $row->addColumn(new LayoutTableColumn('table'));
                $row->addColumn(new LayoutTableColumn($cmsTable->table));
                $table->addRow($row);
            }

            $widget1->addTable($table);

        }
        $page->addWidget($widget1);

        $response->addUIElement($page);

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
