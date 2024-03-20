<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use Illuminate\Http\Request as HttpRequest;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Template;
use NotFound\Framework\Services\Editor\FieldsProperties;
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

class CmsEditorTemplateController extends \NotFound\Framework\Http\Controllers\Controller
{
    public function index()
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('CMS Editor', 'Page Templates');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('Page Templates');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget('Edit templates', 6);

        $table = new LayoutTable(delete: false, edit: true, create: false, sort: false);
        $table->addHeader(new LayoutTableHeader('Templates', 'table'));

        $tables = Template::orderBy('name')->get();

        foreach ($tables as $cmsTable) {
            $row = new LayoutTableRow($cmsTable->id, '/app/editor/page/'.$cmsTable->id);
            $row->addColumn(new LayoutTableColumn($cmsTable->name, 'table'));
            $table->addRow($row);
        }
        $widget1->addTable($table);
        $widget1->noPadding();

        $page->addWidget($widget1);

        $widget2 = new LayoutWidget('Add new template', 6);
        $form = new LayoutForm('/app/editor/page/');

        $form->addInput((new LayoutInputText('name', 'Display name'))->setRequired());
        $form->addInput((new LayoutInputText('internal', 'Internal (file)name'))->setRequired());

        $form->addButton(new LayoutButton('Add new template'));
        $widget2->addForm($form);

        $form = new LayoutForm('/app/editor/template-export/');

        $form->addInput((new LayoutInputCheckbox('name', 'I know this will overwrite the template files from my database'))->setRequired());

        $form->addButton(new LayoutButton('Export all templates to files'));
        $widget2->addForm($form);

        $page->addWidget($widget2);

        $response->addUIElement($page);

        return $response->build();
    }

    public function create(FormDataRequest $request)
    {
        $request->validate(['name' => 'string|required', 'internal' => 'string|required']);
        $table = new Template();
        $tableId = $table->insertGetId([
            'name' => $request->name,
            'filename' => $request->table,
            'enabled' => true,
            'params' => 0,
            'properties' => '{}',
        ]);
        $response = new LayoutResponse();

        $response->addAction(new Toast('Template added'));
        $action = new Redirect('/app/editor/page/'.$tableId);
        $response->addAction($action);

        return $response->build();
    }

    public function readOne(Template $table)
    {
        $response = new LayoutResponse();

        $page = new LayoutPage('CMS Editor', 'Page');

        $breadcrumbs = new LayoutBreadcrumb();
        $breadcrumbs->addHome();
        $breadcrumbs->addItem('CMS Editor', '/app/editor/');
        $breadcrumbs->addItem('Page Templates', '/app/editor/page/');
        $breadcrumbs->addItem($table->name ?? 'New template');
        $page->addBreadCrumb($breadcrumbs);

        $widget1 = new LayoutWidget($table->name ?? 'New template', 6);

        $form = new LayoutForm('/app/editor/page/'.$table->id);

        $form->addInput((new LayoutInputText('name', 'Name'))->setValue($table->name ?? '')->setRequired());
        $form->addInput((new LayoutInputText('filename', 'Filename'))->setValue($table->filename ?? '')->setRequired());
        $form->addInput((new LayoutInputText('params', 'Url parameters'))->setValue($table->params ?? ''));
        $form->addInput((new LayoutInputText('allow_children', "Subpagina's"))->setValue($table->allow_children ?? '')); // TODO: Make this a tableselect that gets the existing cms_templates

        $form->addInput((new LayoutInputCheckbox('enabled', 'Active'))->setValue($this->forceBool($table->enabled)));
        $form->addInput((new LayoutInputCheckbox('meta', 'Add meta fields'))->setValue($this->forceBool($table->properties->meta ?? false)));

        $form->addInput((new LayoutInputCheckbox('searchable', 'Searchable via SOLR'))->setValue($this->forceBool($table->properties->searchable ?? false)));

        $form->addButton(new LayoutButton('Update template properties'));
        $widget1->addTitle((new LayoutTitle('Edit template'))->setSize(4));
        $widget1->addForm($form);

        $newFieldForm = new LayoutForm('/app/editor/page/'.$table->id.'/add-field');

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

        $widget2 = new LayoutWidget('Template items', 6);
        $widget2->noPadding();

        $UItable = new LayoutTable(delete: false, edit: true, create: false);

        $UItable->addHeader(new LayoutTableHeader('Element', 'table'));
        $UItable->addHeader(new LayoutTableHeader('Type', 'type'));
        $UItable->addHeader(new LayoutTableHeader('Internal name', 'internal'));
        $UItable->addHeader(new LayoutTableHeader('Enabled', 'enabled'));
        $tables = $table->items()->orderBy('order', 'asc')->get();

        foreach ($tables as $cmsTable) {
            $row = new LayoutTableRow($cmsTable->id, '/app/editor/page/'.$table->id.'/'.$cmsTable->id);
            $row->addColumn(new LayoutTableColumn($cmsTable->name, 'text'));
            $row->addColumn(new LayoutTableColumn($cmsTable->type, 'text'));
            $row->addColumn(new LayoutTableColumn($cmsTable->internal, 'internal'));

            $checkbox = new LayoutTableColumn($cmsTable->enabled, 'checkbox');
            $checkbox->setToggleEndPoint('/app/editor/page/'.$table->id.'/'.$cmsTable->id.'/enabled');
            $row->addColumn($checkbox);

            $UItable->addRow($row);
        }
        $widget2->addTable($UItable);

        $page->addWidget($widget2);

        $response->addUIElement($page);

        return $response->build();
    }

    public function update(FormDataRequest $request, Template $table)
    {
        $request->validate([
            'name' => 'string|required',
            'filename' => 'string|required',
            'params' => 'string|nullable',
            'allow_children' => 'string|nullable',
            'enabled' => 'boolean',
            'meta' => 'boolean',
            'searchable' => 'boolean',
        ]);

        $properties = (object) array_merge(
            (array) $table->properties,
            [
                'disable_sticky_submit' => $request->disable_sticky_submit,
                //    'stay_on_page' => $request->stay_on_page,
                'meta' => $request->meta,
                'searchable' => $request->searchable,
            ]
        );

        $table->update([
            'name' => $request->name,
            'filename' => $request->filename,
            'params' => $request->params,
            'allow_children' => $request->allow_children,
            'enabled' => $request->enabled,
            'properties' => $properties,
        ]);
        $response = new LayoutResponse();
        $response->addAction(new Toast('Template properties updated'));

        return $response->build();
    }

    public function addField(FormDataRequest $request, Template $table)
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
            $response->addAction(new Redirect('/app/editor/page/'.$table->id.'/'.$newField->id));
        }

        return $response->build();
    }

    public function updatePosition(HttpRequest $request, Template $table)
    {
        $request->validate([
            'recordId' => 'required|int',
            'replacedRecordId' => 'required|int',
        ]);

        $response = new LayoutResponse();
        try {
            db_table_items_change_order('cms_templateitem', $request->recordId, $request->replacedRecordId, "AND `template` = {$table->id}");
        } catch (\Exception $e) {
            $response->addAction(new Toast($e->getMessage(), 'error'));

            return $response->build();
        }

        return response()->json(['status' => 'ok']);
    }

    private function forceBool(mixed $value): bool
    {
        if (is_null($value)) {
            return false;
        }
        if (is_bool($value)) {
            return $value;
        }
        if ($value == 1) {
            return true;
        }

        return false;
    }
}
