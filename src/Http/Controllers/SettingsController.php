<?php

namespace NotFound\Framework\Http\Controllers;

use Illuminate\Http\Request;
use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\CmsConfig;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\Table\LayoutTable;
use NotFound\Layout\Elements\Table\LayoutTableColumn;
use NotFound\Layout\Elements\Table\LayoutTableHeader;
use NotFound\Layout\Elements\Table\LayoutTableRow;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Redirect;
use NotFound\Layout\Responses\Toast;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $helper = new LayoutWidgetHelper('Website instellingen', 'Instellingen');
        $helper->widget->noPadding();

        $table = new LayoutTable(delete: false, sort: false, create: false);

        $table->addHeader(new LayoutTableHeader('Naam', 'name'));
        $table->addHeader(new LayoutTableHeader('Ingestelde waarde', 'value'));

        foreach (CmsConfig::all() as $setting) {
            // Check if the user can edit this setting
            if ($request->user()->can('view', $setting)) {
                $row = new LayoutTableRow($setting->id, '/app/settings/'.$setting->id);
                $row->addColumn(new LayoutTableColumn($setting->name));

                $coumnText = $setting->type == 2 ? 'Meerdere waarden' : $setting->value;
                $row->addColumn(new LayoutTableColumn($coumnText));

                $table->addRow($row);
            }
        }
        $helper->widget->addTable($table);

        return $helper->response();
    }

    public function readOne(Request $request, CmsConfig $setting)
    {
        $helper = new LayoutWidgetHelper('Website instellingen', $setting->name);
        $helper->addBreadcrumb('Instellingen', '/app/settings');

        // Only allow editing if the user has the right to do so
        if ($request->user()->cannot('update', $setting)) {
            $helper->widget->addText(new LayoutText('Je hebt geen rechten om deze instelling te wijzigen.'));

            return $helper->response();
        }

        if ($setting->description != null) {
            $helper->widget->addText(new LayoutText($setting->description));
        }
        $form = new LayoutForm('app/settings/'.$setting->id);

        if ($setting->hasJsonValue()) {
            $jsonValue = json_decode($setting->value ?? '{}');

            foreach ($jsonValue as $key => $value) {
                $valueInput = new LayoutInputText($key, ucfirst($key));
                $valueInput->setValue(strval($value) ?? '');
                $form->addInput($valueInput);
            }
        } else {
            $valueInput = new LayoutInputText('value', 'Waarde');
            $valueInput->setValue(strval($setting->value) ?? '')->setRequired();
            $form->addInput($valueInput);
        }

        $form->addButton(new LayoutButton('Opslaan'));

        $helper->widget->addForm($form);

        return $helper->response();
    }

    public function update(FormDataRequest $request, CmsConfig $setting)
    {
        $response = new LayoutResponse;
        // Only allow editing if the user has the right to do so
        if ($request->user()->cannot('update', $setting)) {
            $action = new Toast('Onvoldoende rechten', 'error');
            $response->addAction($action);

            return $response->build();
        }

        if ($setting->hasJsonValue()) {
            $jsonValue = json_decode($setting->value ?? '{}');

            foreach ($jsonValue as $key => $value) {
                $validated = $request->validate([
                    $key => 'string|nullable',
                ]);

                $newValue = $validated[$key];
                if ($newValue === null) {
                    $newValue = '';
                }

                $jsonValue->{$key} = $newValue;
            }

            $setting->value = json_encode($jsonValue);
        } else {
            $request->validate([
                'value' => 'required|string',
            ]);

            $setting->value = $request->value;
        }

        $setting->save();

        $response->addAction(new Toast('Instelling opgeslagen', 'ok'));
        $response->addAction(new Redirect('/app/settings'));

        return $response->build();
    }
}
