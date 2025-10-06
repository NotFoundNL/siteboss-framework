<?php

namespace NotFound\Framework\Http\Controllers\CmsEditor;

use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\Table;
use NotFound\Framework\Models\TableItem;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;
use stdClass;

class CmsEditorTableItemController extends \NotFound\Framework\Http\Controllers\Controller
{
    public function readOne(Table $table, TableItem $tableItem)
    {
        $widgetPage = new LayoutWidgetHelper(pageTitle: 'CMS Editor', widgetTitle: $tableItem->name);
        $widgetPage->addBreadcrumb('CMS Editor', '/app/editor/');
        $widgetPage->addBreadcrumb('Tables', '/app/editor/table/');
        $widgetPage->addBreadcrumb($table->name, '/app/editor/table/'.$table->id);

        $form = new LayoutForm('/app/editor/table/'.$table->id.'/'.$tableItem->id);

        $nameInput = new LayoutInputText('name', 'Name');

        $nameInput->setValue($tableItem->name);
        $nameInput->setRequired();
        $form->addInput($nameInput);

        $internalInput = new LayoutInputText('internal', 'Internal name');
        $internalInput->setValue($tableItem->internal);
        $internalInput->setRequired();

        $form->addInput($internalInput);

        $internalInput = new LayoutInputText('description', 'Description');
        $internalInput->setValue($tableItem->description ?? '');

        $form->addInput($internalInput);

        $internalInput = new LayoutInputCheckbox('enabled', 'Enabled');
        $internalInput->setValue($tableItem->enabled);
        $form->addInput($internalInput);

        $type = ucfirst($tableItem->type);

        $properties = (object) array_merge((array) $tableItem->properties, (array) $tableItem->server_properties);

        $fieldProperties = new \NotFound\Framework\Services\Editor\FieldsProperties($properties ?? new stdClass);
        $fieldProperties->getLayoutFields($type, $form);

        $form->addButton(new LayoutButton('Save field properties'));

        $widgetPage->widget->addForm($form);

        return $widgetPage->response();
    }

    /**
     * update
     *
     * @param  mixed  $table
     * @param  mixed  $tableItem
     * @return void
     */
    public function update(FormDataRequest $request, Table $table, TableItem $tableItem)
    {
        $request->validate([
            'name' => 'string|required',
            'internal' => 'string|required',
            'description' => 'string',
            'enabled' => 'boolean',
        ]);

        $tableItem->name = $request->name;
        $tableItem->internal = $request->internal;
        $tableItem->description = $request->description;
        $tableItem->enabled = $request->enabled;
        $properties = (object) array_merge((array) $tableItem->properties, (array) $tableItem->server_properties);

        $fieldProperties = new \NotFound\Framework\Services\Editor\FieldsProperties($properties);
        $tableItem->properties = $fieldProperties->updateProperties($tableItem->type, $request);
        $tableItem->server_properties = $fieldProperties->updateServerProperties($tableItem->type, $request);

        $tableItem->save();
        $response = new LayoutResponse;
        $response->addAction(new Toast('Field properties updated'));
        $response->addAction(new Redirect('/app/editor/table/'.$table->id.'/'));

        $table->exportToFile();

        return $response->build();
    }

    /**
     * enabled
     *
     * @param  mixed  $table
     * @param  mixed  $tableItem
     * @return void
     */
    public function enabled(Table $table, TableItem $tableItem)
    {
        $tableItem->enabled = ! $tableItem->enabled;
        try {
            $tableItem->save();

            $response = ['value' => $tableItem->enabled, 'message' => 'Item updated'];
        } catch (\Exception $e) {
            $response = ['error' => $e];
        }

        $table->exportToFile();

        return $response;
    }
}
