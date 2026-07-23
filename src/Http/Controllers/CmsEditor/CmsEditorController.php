<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTable;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Framework\Helpers\Layout\Elements\Table\LayoutTableRow;
use NotFound\Framework\Helpers\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Framework\Http\Controllers\Controller;

class CmsEditorController extends Controller
{
    public function index()
    {
        $widgetPage = new LayoutWidgetHelper(pageTitle: 'CMS Editor', widgetTitle: 'CMS Editor');
        $widgetPage->widget->noPadding();

        $table = new LayoutTable(
            delete: false,
            edit: true,
            create: false,
            sort: false
        );
        $table->addHeader(new LayoutTableHeader('Main menu', 'table'));

        $row = new LayoutTableRow(1, '/app/editor/table/');
        $row->addColumn(new LayoutTableColumn('Tables'));
        $table->addRow($row);

        $row = new LayoutTableRow(2, '/app/editor/page/');
        $row->addColumn(new LayoutTableColumn('Page Templates'));
        $table->addRow($row);

        $row = new LayoutTableRow(3, '/app/editor/menu/');
        $row->addColumn(new LayoutTableColumn('CMS Menu'));
        $table->addRow($row);

        $row = new LayoutTableRow(4, '/app/editor/lang/');
        $row->addColumn(new LayoutTableColumn('Language'));
        $table->addRow($row);
        $widgetPage->widget->addTable($table);

        return $widgetPage->response();
    }
}
