<?php

namespace NotFound\Framework\Http\Controllers;

use App\Http\Requests\FormDataRequest;
use App\Models\Table;
use App\Services\Editor\FieldsProperties;
use Illuminate\Http\Request as HttpRequest;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutPage;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Elements\LayoutWidget;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputDropdown;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class CmsEditorTableController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('CMS Editor', 'Table');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('Tables');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget('Tables', 6);

        $table = new LayoutTable(delete: false, edit: true, create: false, sort: false);
        $table->addHeader(new LayoutTableHeader('Table', 'table'));

        $tables = Table::orderBy('name')->get();

        foreach ($tables as $cmsTable) {
            $row = new LayoutTableRow($cmsTable->id, '/app/editor/table/'.$cmsTable->id);
            $row->addColumn(new LayoutTableColumn($cmsTable->name, 'table'));
            $table->addRow($row);
        }
        $widget1->addTable($table);
        $widget1->noPadding();

        $page->addWidget($widget1);

        $widget2 = new LayoutWidget('Add new table', 6);
        $form = new LayoutForm('/app/editor/table/');

        $form->addInput((new LayoutInputText('name', 'Display name'))->setRequired());
        $form->addInput((new LayoutInputText('table', 'Table name'))->setRequired());

        $form->addButton(new LayoutButton('Add new table'));
        $widget2->addForm($form);

        $page->addWidget($widget2);

        $response->addUIElement($page);

        return $response->build();
    }

    public function create(FormDataRequest $request)
    {
        $request->validate(['name' => 'string|required', 'table' => 'string|required']);
        $table = new Table();
        $tableId = $table->insertGetId([
            'name' => $request->name,
            'table' => $request->table,
            'url' => $request->table,
            'enabled' => true,
            'allow_create' => true,
            'allow_delete' => true,
            'allow_sort' => false,
        ]);
        $response = new LayoutResponse();

        $response->addAction(new Toast('Table added'));
        $action = new Redirect('/app/editor/table/'.$tableId);
        $response->addAction($action);

        return $response->build();
    }

    public function readOne(Table $table)
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('CMS Editor', 'Table');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('Tables', '/app/editor/table/');
        $breadcrumbs->addItem($table->name ?? 'New table');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget($table->name ?? 'New table', 6);

        $form = new LayoutForm('/app/editor/table/'.$table->id);

        $form->addInput((new LayoutInputText('name', 'Name'))->setValue($table->name ?? '')->setRequired());
        $form->addInput((new LayoutInputText('table', 'Table'))->setValue($table->table ?? '')->setRequired());

        $form->addInput((new LayoutInputText('url', 'Slug'))->setValue($table->url ?? '')->setRequired());

        $form->addInput((new LayoutInputText('itemsPerPage', 'Rows per page'))->setValue($table->properties->itemsPerPage ?? '25')->setRequired());

        $form->addInput((new LayoutInputCheckbox('enabled', 'Active'))->setValue($table->enabled == 1 ?? false));
        $form->addInput((new LayoutInputCheckbox('allow_create', 'Allow create'))->setValue($table->allow_create == 1 ?? false));
        $form->addInput((new LayoutInputCheckbox('allow_delete', 'Allow delete'))->setValue($table->allow_delete == 1 ?? false));
        $form->addInput((new LayoutInputCheckbox('allow_sort', 'Allow sorting'))->setValue($table->allow_sort == 1 ?? false));
        $form->addInput((new LayoutInputCheckbox('disable_sticky_submit', 'Disable sticky submit button'))->setValue($table->properties->disable_sticky_submit ?? false));
        $form->addInput((new LayoutInputCheckbox('stay_on_page', 'Allow stay on page'))->setValue($table->properties->stay_on_page ?? false));
        $form->addInput((new LayoutInputCheckbox('localize', 'Localize this table'))->setValue($table->properties->localize ?? false));

        $form->addButton(new LayoutButton('Update table properties'));
        $widget1->addTitle((new LayoutTitle('Edit table'))->setSize(4));
        $widget1->addForm($form);

        $newFieldForm = new LayoutForm('/app/editor/table/'.$table->id.'/add-field');

        $newFieldDropDown = new LayoutInputDropdown('new_field', 'New field');
        $newFieldDropDown->setRequired();

        $fields = (new FieldsProperties())->availableFields();
        foreach ($fields as $field) {
            $newFieldDropDown->addItem($field);
        }

        $newFieldForm->addInput($newFieldDropDown);

        $newFieldForm->addInput((new LayoutInputText('name', 'Display name'))->setRequired());
        $newFieldForm->addInput((new LayoutInputText('internal', 'Internal'))->setRequired());
        $newFieldForm->addButton(new LayoutButton('Add field'));

        $widget1->addTitle((new LayoutTitle('Add new field'))->setSize(4));

        $widget1->addForm($newFieldForm);

        $page->addWidget($widget1);

        $widget2 = new LayoutWidget('Table items', 6);
        $widget2->noPadding();

        $UItable = new LayoutTable(delete: false, edit: true, create: false);

        $UItable->addHeader(new LayoutTableHeader('Element', 'table'));
        $UItable->addHeader(new LayoutTableHeader('Type', 'type'));
        $UItable->addHeader(new LayoutTableHeader('Internal name', 'internal'));
        $UItable->addHeader(new LayoutTableHeader('Enabled', 'enabled'));
        $tables = $table->items()->orderBy('order', 'asc')->get();

        foreach ($tables as $cmsTable) {
            $row = new LayoutTableRow($cmsTable->id, '/app/editor/table/'.$table->id.'/'.$cmsTable->id);
            $row->addColumn(new LayoutTableColumn($cmsTable->name, 'text'));
            $row->addColumn(new LayoutTableColumn($cmsTable->type, 'text'));
            $row->addColumn(new LayoutTableColumn($cmsTable->internal, 'text'));
            $row->addColumn(new LayoutTableColumn($cmsTable->enabled, 'checkbox'));
            $UItable->addRow($row);
        }
        $widget2->addTable($UItable);

        $page->addWidget($widget2);
        $response->addUIElement($page);

        return $response->build();
    }

    public function update(FormDataRequest $request, Table $table)
    {
        $request->validate([
            'name' => 'string|required',
            'table' => 'string|required',
            'url' => 'string|required',
            'enabled' => 'boolean',
            'allow_create' => 'boolean',
            'allow_delete' => 'boolean',
            'allow_sort' => 'boolean',
            'disable_sticky_submit' => 'boolean',
            'stay_on_page' => 'boolean',
            'localize' => 'boolean',
            'itemsPerPage' => 'integer|required',
        ]);

        $properties = (object) array_merge(
            (array) $table->properties,
            [
                'disable_sticky_submit' => $request->disable_sticky_submit,
                'stay_on_page' => $request->stay_on_page,
                'localize' => $request->localize,
                'itemsPerPage' => $request->itemsPerPage,
            ]
        );

        $table->update([
            'name' => $request->name,
            'table' => $request->table,
            'url' => $request->url,
            'enabled' => $request->enabled,
            'allow_create' => $request->allow_create,
            'allow_delete' => $request->allow_delete,
            'allow_sort' => $request->allow_sort,
            'properties' => $properties,
        ]);
        $response = new LayoutResponse();
        $response->addAction(new Toast('Table properties updated'));

        return $response->build();
    }

    public function addField(FormDataRequest $request, Table $table)
    {
        $request->validate([
            'name' => 'string|required',
            'internal' => 'string|required',
        ]);

        $response = new LayoutResponse();
        $response->addAction(new Toast('Table properties updated'));

        $fields = (new FieldsProperties())->availableFields();

        $max = $table->items()->max('order') + 1;
        if (! in_array($request->new_field, $fields)) {
            $response->addAction(new Toast('Field not found', 'error'));
        } else {
            $newField = $table->items()->create([
                'order' => $max,
                'name' => $request->name,
                'type' => $request->new_field,
                'internal' => $request->internal,
            ]);
            $response->addAction(new Redirect('/app/editor/table/'.$table->id.'/'.$newField->id));
        }

        return $response->build();
    }

    public function updatePosition(HttpRequest $request, Table $table)
    {
        $request->validate([
            'recordId' => 'required|int',
            'replacedRecordId' => 'required|int',
        ]);

        $response = new LayoutResponse();
        try {
            db_table_items_change_order('cms_tableitem', $request->recordId, $request->replacedRecordId, "AND `table_id` = {$table->id}");
        } catch (\Exception $e) {
            $response->addAction(new Toast($e->getMessage(), 'error'));

            return $response->build();
        }

        return response()->json(['status' => 'ok']);
    }
}
