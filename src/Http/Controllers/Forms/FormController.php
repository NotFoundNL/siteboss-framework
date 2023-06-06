<?php

namespace NotFound\Framework\Http\Controllers\Forms;

use Illuminate\Http\Request;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Forms\Category;
use NotFound\Framework\Models\Forms\Field;
use NotFound\Framework\Models\Forms\Form;

class FormController extends Controller
{
    public function readAllCombinations(Form $form)
    {
        return $form
            ->whereTypeCombination()
            ->where('archived', 0)
            ->orWhere('archived', null)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function readAllArchive(Form $form)
    {
        return $form
            ->where('archived', 1)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function readAllBasedOnCategory(Form $form, Category $category)
    {
        $this->authorize('view', $category);

        //TODO: show number of applications
        return $form->getByCategory($category->slug);
    }

    public function updateText(Request $request, Form $form)
    {
        $this->authorize('viewText', Form::class);

        $request->validate([
            'texts' => 'required',
        ]);

        $form->success_text = $request->texts['success_text'];
        $form->confirmation_mail = $request->texts['confirmation_mail'];
        $form->save();

        return $this->getText($form);
    }

    public function getText(Form $form)
    {
        $this->authorize('viewText', Form::class);

        $fieldsController = new FieldController(new Field());

        $result = (object) [];
        $result->fields = $fieldsController->listFields($form->id);
        $result->success_text = $form->success_text ?? new \stdClass;
        $result->confirmation_mail = $form->confirmation_mail ?? new \stdClass;

        return ['form' => $result];
    }

    public function create(Request $request)
    {
        $this->authorize('create', Form::class);

        $request->validate([
            'name' => 'required|max:255',
            'type' => 'required',
        ]);

        $form = new Form();
        $form->name = $request->name;
        $form->type = $request->type;

        if ($request->type == 'form') {
            $category = Category::whereSlug($request->category)->first();
            $form->category_id = $category->id;

            $form->notification_address = $request->mail ?? '';
        }

        try {
            return $form->save();
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                return response()->json([
                    'message' => 'Form with the same name already exists',
                ], 409);
            }

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Form $form)
    {
        $this->authorize('update', $form->category);

        $request->validate([
            'name' => 'required',
        ]);

        $form->name = $request->name;
        $form->notification_address = $request->mail ?? '';
        $form->archived = $request->archived ?? $form->archived;

        return $form->save();
    }

    public function delete(Form $form)
    {
        $this->authorize('delete', $form);

        return $form->delete();
    }

    public function clone(Request $request, Form $form)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $newForm = $form->replicate();
        $newForm->name = $request->name;
        $newForm->save();

        $fieldC = new Field();
        $fieldC->cloneToNewForm($newForm, $form);

        return $newForm;
    }
}
