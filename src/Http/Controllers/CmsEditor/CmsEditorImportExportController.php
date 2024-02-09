<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Controllers\Controller;
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

    public function exportAllTemplates()
    {
        $response = new LayoutResponse();
        $templates = Template::all();

        foreach ($templates as $template) {
            // TODO: catch exceptions
            $template->exportToFile();
        }

        $response->addAction(
            new Toast($templates->count().' templates exported successfully')
        );

        return $response->build();
    }
}
