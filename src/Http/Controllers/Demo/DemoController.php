<?php

namespace NotFound\Framework\Http\Controllers\Demo;

use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutTab;
use NotFound\Layout\Elements\LayoutTabs;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;

class DemoController extends \NotFound\Framework\Http\Controllers\Controller
{
    public function index()
    {
        $response = new LayoutResponse;
        $page = new LayoutPage(__('siteboss::ui.page'));
        $breadcrumb = new LayoutBreadcrumb;
        $breadcrumb->addHome();
        $breadcrumb->addItem('Demo', '/app/demo/');
        $breadcrumb->addItem('Grid');
        $page->addBreadCrumb($breadcrumb);

        $form = new LayoutForm('test');

        $demoContent = new LayoutInputText('test', 'Dit is een veld');

        $form->addInput($demoContent);

        $row = new \NotFound\Layout\Elements\LayoutRow;
        $widget = new LayoutWidget('Embedded', 6);
        $widget->addForm($form);
        $widget->addForm($form);
        $row->addWidget($widget);
        $row->addWidget($widget);

        $widget = new LayoutWidget('First full width widget', 12);

        $tabs = new LayoutTabs;
        $tabs->addTab(new LayoutTab('Grid', '/app/demo/grid/'))->addTab(new LayoutTab('Misc', '/app/demo/misc/'));

        $widget->addTabs($tabs);

        $widget->addForm($form);

        $colsRow = new \NotFound\Layout\Elements\LayoutRow;
        $colsRow->addForm($form);
        $colsRow->addForm($form);

        $widget->addRow($colsRow);
        $widget->addRow($row);
        $page->addWidget($widget);
        $widget = new LayoutWidget('Second full width widget', 12);
        $widget->addForm($form);
        $page->addWidget($widget);

        $widget = new LayoutWidget('First half width widget', 6);
        $widget->addForm($form);
        $page->addWidget($widget);
        $widget = new LayoutWidget('Second half width widget', 6);
        $widget->addForm($form);
        $page->addWidget($widget); //      $layoutTable = new LayoutTable($headerArray, $contentArray);

        $response->addUIElement($page);

        return $response->build();
    }
}
