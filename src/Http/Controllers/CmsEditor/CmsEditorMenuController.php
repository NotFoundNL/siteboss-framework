<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\CmsMenu;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Log;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputDropdown;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class CmsEditorMenuController extends \NotFound\Framework\Http\Controllers\Controller
{
    public function index()
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('CMS Editor');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('CMS Menu');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget('CMS Menu', 12);

        $table = new LayoutTable(delete: true, edit: true, create: true, sort: true);
        $table->addHeader(new LayoutTableHeader('Name', 'table'));
        $table->addHeader(new LayoutTableHeader('Level', 'level'));
        $table->addHeader(new LayoutTableHeader('Icon', 'icon'));
        $table->addHeader(new LayoutTableHeader('Rights', 'rights'));
        $table->addHeader(new LayoutTableHeader('Enabled', 'enabled'));
        $tables = CmsMenu::orderBy('order')->get();

        foreach ($tables as $cmsTable) {
            $row = new LayoutTableRow($cmsTable->id, '/app/editor/menu/'.$cmsTable->id);
            $row->addColumn(new LayoutTableColumn($cmsTable->level == 1 ? '- '.$cmsTable->title : $cmsTable->title, 'table'));
            $row->addColumn(new LayoutTableColumn($cmsTable->level, 'level'));
            $row->addColumn(new LayoutTableColumn($cmsTable->icon, 'icon'));
            $row->addColumn(new LayoutTableColumn($cmsTable->rights, 'rights'));
            $row->addColumn(new LayoutTableColumn($cmsTable->enabled, 'enabled'));
            $table->addRow($row);
        }
        $widget1->addTable($table);
        $widget1->noPadding();

        $page->addWidget($widget1);

        $widget2 = new LayoutWidget('Add new item', 12);

        $newFieldForm = new LayoutForm('/app/editor/menu/');

        $newFieldForm->addInput((new LayoutInputText('name', 'Display name'))->setRequired());
        $newFieldForm->addInput((new LayoutInputText('to', 'Target Path'))->setRequired());
        $newFieldForm->addButton(new LayoutButton('Add menuitem'));

        $widget2->addForm($newFieldForm);

        $page->addWidget($widget2);

        $response->addUIElement($page);

        return $response->build();
    }

    public function readOne(CmsMenu $menuItem)
    {
        $widgetPage = new LayoutWidgetHelper(pageTitle: 'CMS Editor', widgetTitle: $menuItem->title ?? 'Menu item');
        $widgetPage->addBreadcrumb('CMS Editor', '/app/editor/');
        $widgetPage->addBreadcrumb('Menu', '/app/editor/menu/');

        $form = new LayoutForm('/app/editor/menu/'.$menuItem->id);

        $nameInput = new LayoutInputText('title', 'Name');
        $nameInput->setValue($menuItem->title);
        $nameInput->setRequired();
        $form->addInput($nameInput);

        $nameInput = new LayoutInputText('to', 'To');
        $nameInput->setValue($menuItem->to);
        $nameInput->setRequired();
        $form->addInput($nameInput);
        $internalInput = new LayoutInputText('icon', 'Icon');
        $internalInput->setValue($menuItem->icon);
        $form->addInput($internalInput);

        $internalInput = new LayoutInputText('rights', 'Rights');
        $internalInput->setValue($menuItem->rights);
        $form->addInput($internalInput);

        $internalInput = new LayoutInputCheckbox('enabled', 'enabled');
        $internalInput->setValue($menuItem->enabled);
        $form->addInput($internalInput);

        $typeInput = new LayoutInputDropdown('level', 'Level');
        $typeInput->setValue($menuItem->level);
        $typeInput->setDescription('When updating the type, the properties will be reset. So save these settings before making other changes.');

        $typeInput->addItem(0, 'Main');
        $typeInput->addItem(1, 'Subitem');

        $form->addInput($typeInput);

        // We'll read the JSON to get more properties

        $form->addButton(new LayoutButton(__('siteboss::ui.save')));

        $widgetPage->widget->addForm($form);

        return $widgetPage->response();
    }

    public function update(FormDataRequest $request, CmsMenu $menuItem)
    {
        $menuItem->update($request->validate([
            'title' => 'string|required',
            'to' => 'string|required', 'icon' => 'string', 'rights' => 'string',
            'level' => 'int|required',
            'enabled' => 'boolean',
        ]));
        $response = new LayoutResponse();
        $response->addAction(new Toast('Field properties updated'));

        return $response->build();
    }

    public function updatePosition(HttpRequest $request)
    {
        $request->validate([
            'recordId' => 'required|int',
            'replacedRecordId' => 'required|int',
        ]);

        $response = new LayoutResponse();
        try {
            db_table_items_change_order('cms_menu', $request->recordId, $request->replacedRecordId);
        } catch (\Exception $e) {
            $response->addAction(new Toast($e->getMessage(), 'error'));

            return $response->build();
        }

        return response()->json(['status' => 'ok']);
    }

    public function deleteRecord(int $recordId)
    {
        //  $this->authorize('delete', CmsMenu::class);
        Log::withContext(['record-id' => $recordId])->notice('Menuitem deleted');

        if (CmsMenu::where('id', $recordId)->delete()) {
            return response()->json(['status' => 'ok']);
        }

        abort(404, __('response.table.delete'));
    }

    public function addItem(FormDataRequest $request)
    {
        $request->validate([
            'name' => 'string|required',
            'to' => 'string|required',
        ]);

        $response = new LayoutResponse();
        $response->addAction(new Toast('Table properties updated'));

        $max = CmsMenu::max('order') + 1;
        $newField = CmsMenu::create([
            'order' => $max,
            'title' => $request->name,
            'to' => $request->to,
            'icon' => 'list',
            'enabled' => true,
            'level' => 0,
        ]);
        $response->addAction(new Redirect('/app/editor/menu/'.$newField->id));

        return $response->build();
    }
}
