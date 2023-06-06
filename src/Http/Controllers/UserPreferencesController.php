<?php

namespace NotFound\Framework\Http\Controllers;

use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Framework\Models\CmsUser;
use NotFound\Layout\Elements\LayoutButton;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Helpers\LayoutWidgetHelper;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\LayoutResponse;
use NotFound\Layout\Responses\Toast;
use stdClass;

class UserPreferencesController extends Controller
{
    public function index()
    {
        $widget = new LayoutWidgetHelper(__('siteboss::preferences.title'), __('siteboss::preferences.subtitle'));
        $widget->addBreadcrumb(__('siteboss::preferences.title'));

        $form = new LayoutForm('app/preferences/');

        $preferences = $this->getUser()->preferences;

        foreach ($this->settings() as $setting) {
            switch ($setting->type) {
                case 'checkbox':
                    $settingField = new LayoutInputCheckbox($setting->name, __('siteboss::preferences.settings.'.$setting->name));
                    $settingField->setValue($preferences->{$setting->name} ?? false);
                    $form->addInput($settingField);
                    break;
                default:
                    $header = new LayoutTitle(__('siteboss::preferences.settings.'.$setting->name));
                    $form->addTitle($header);
            }
        }

        $form->addButton(new LayoutButton(__('siteboss::preferences.save'), 'submit', 'primary'));
        $widget->widget->addForm($form);

        return $widget->response();
    }

    public function update(FormDataRequest $request)
    {
        $user = $this->getUser();

        $preferences = new stdClass();

        foreach ($this->settings() as $setting) {
            switch ($setting->type) {
                case 'checkbox':
                    $preferences->{$setting->name} = boolval($request->input($setting->name));
                    break;
                default:
                    // do nothing
            }
        }
        $user->preferences = $preferences;
        $user->save();

        $response = new LayoutResponse();
        $action = new Toast(__('siteboss::preferences.saved'), 'ok');
        $response->addAction($action);

        return $response->build();
    }

    private function getUser(): CmsUser
    {
        $user = auth()->user();

        return CmsUser::find($user->id);
    }

    private function settings(): array
    {
        return [
            (object) [
                'type' => 'header',
                'name' => 'interface',
            ],
            (object) [
                'name' => 'no_animation',
                'type' => 'checkbox',
            ], (object) [
                'name' => 'auto_select',
                'type' => 'checkbox',
            ], (object) [
                'name' => 'no_sound',
                'type' => 'checkbox',
            ],
        ];
    }
}
