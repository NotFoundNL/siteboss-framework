<?php

namespace NotFound\Framework\Http\Controllers\Forms;

use Illuminate\Http\Request;
use NotFound\Framework\Helpers\SitebossHelper;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Forms\Data;
use NotFound\Framework\Models\Forms\Field;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Services\Forms\MailHandler;
use NotFound\Framework\Services\Legacy\StatusColumn;

class DataController extends Controller
{
    // Post form from client frontend
    public function create($formId, $langurl)
    {
        $lang = Lang::where('url', $langurl)->first();
        // TODO: BUG: Fail for all multi page forms
        $fieldC = new Field;
        $originalForm = $fieldC->getWithChildren($formId);

        abort_if(
            empty($originalForm),
            400,
            'Something went wrong (E01)'
        );

        $formValidator = new \NotFound\Framework\Services\Forms\FormValidator($originalForm, $langurl, $formId);

        $formValidator->validate();

        if ($formValidator->hasErrors()) {
            return response($formValidator->jsonErrors());
        }

        $formValidator->beforeSave();

        $submittedForm = Data::create([
            'form_id' => $formId,
            'ip_address' => request()->ip(),
        ]);

        $formValidator->setRecordId($submittedForm->id);

        $submittedForm->data = $formValidator->getDataForSqlQuery();
        $submittedForm->save();

        if (! $submittedForm) {
            $this->setStatusCode(400);
            $formValidator->rollback($submittedForm->id);

            abort(400, "Couldn't insert values in database.");
        }

        $formValidator->afterSave($submittedForm->id);

        $formInfo = Form::where('id', $formId)->first();

        $mailHandler = new MailHandler($lang, $formInfo, $formValidator->validatorInfo);
        $mailHandler->sendMail();

        $this->runSuccessAction($langurl, $formInfo, $formValidator);

        return response([
            'status' => 'ok',
            'message' => $formInfo->success_text?->{$langurl} ?? '',
        ]);
    }

    public function readOne($id)
    {
        $this->authorize('viewAny', Data::class);
        $dataHandler = new \NotFound\Framework\Services\Forms\UserDataTransformer($id);

        return $dataHandler->getDataTable();
    }

    public function readOneFilled($id)
    {
        $this->authorize('viewAny', Data::class);
        $dataHandler = new \NotFound\Framework\Services\Forms\UserDataTransformer($id, 'filled');

        return $dataHandler->getDataTable();
    }

    public function readOneAll($id)
    {
        $this->authorize('viewAny', Data::class);
        $dataHandler = new \NotFound\Framework\Services\Forms\UserDataTransformer($id, 'all');

        return $dataHandler->getDataTable();
    }

    public function updateField(Request $request, Form $form, $recordId)
    {
        $request->validate([
            'fieldId' => 'required',
            'value' => 'required',
        ]);

        $dataRecord = Data::where('id', $recordId)->first();
        $this->authorize('update', $dataRecord);

        $fieldId = $request->{'fieldId'};

        $newData = $dataRecord->data;
        $newData->{$fieldId}->value = $request->value;
        $dataRecord->data = $newData;
        $dataRecord->save();

        return response()->json(['status' => 'ok']);
    }

    public function deleteRow($formId, $recordId)
    {
        $data = Data::where('id', $recordId)->firstOrFail();
        $this->authorize('delete', $data);

        StatusColumn::deleteModel($data);

        return response()->json(['status' => 'ok']);
    }

    private function runSuccessAction($langurl, $formInfo, $formValidator)
    {
        // Trigger default form handler
        $action = SitebossHelper::config('form_success_action', false) ?? '';
        if ($action && trim($action !== '')) {
            $actionClass = new $action($langurl, $formInfo, $formValidator);
            $actionClass->run();

        } else {

            // TODO: use Laravel events?
            if (trim($formInfo->success_action) != '') {
                $action = trim($formInfo->success_action);

                $actionObj = new $action($langurl, $formInfo, $formValidator);
                $actionObj->run();
            }
        }
    }
}
