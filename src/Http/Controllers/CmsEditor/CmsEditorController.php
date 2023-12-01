<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Helpers\LayoutWidgetHelper;

class CmsEditorController extends \NotFound\Framework\Http\Controllers\Controller
{
    public function index()
    {
        $widgetPage = new LayoutWidgetHelper(pageTitle: 'CMS Editor', widgetTitle: 'CMS Editor');
        $widgetPage->widget->noPadding();

        $table = new LayoutTable(delete: false, edit: true, create: false);
        $table->addHeader(new LayoutTableHeader('Main menu', 'table'));

        $row = new LayoutTableRow(1, '/app/editor/import/');
        $row->addColumn(new LayoutTableColumn('Show CMS changes from filesystem'));
        $table->addRow($row);

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
