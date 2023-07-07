<?php

namespace NotFound\Framework\Http\Controllers\Forms;

use App\Models\Project;
use App\Models\ProjectChallenge;
use Illuminate\Http\Request;
use NotFound\Framework\Http\Controllers\Controller;
use NotFound\Framework\Models\Forms\Field;
use NotFound\Framework\Models\Forms\Form;
use NotFound\Framework\Models\Forms\Property;
use NotFound\Framework\Services\Forms\MimetypeConverter;
use NotFound\Framework\Services\Legacy\StatusColumn;
use stdClass;

class FieldController extends Controller
{
    public function listFields($id)
    {
        $fields = Field::where('form_id', $id)->get();

        $fieldNames = [];

        foreach ($fields as $field) {
            if (strtolower($field->type) == 'combination') {
                continue;
            }

            //TODO: lang
            $newItem = [
                'id' => $field->id,
                'label' => $field->properties->label->{'nl'},
            ];
            $fieldNames[] = $newItem;
        }

        return $fieldNames;
    }

    public function readOne($formId)
    {
        $response = (object) [];

        $response->fields = (new Field)->getWithChildren($formId);

        $form = Form::where('id', $formId)
            ->with('category')
            ->firstOrFail();

        if ($form->category) {
            $this->authorize('view', $form->category);
        }

        $response->category = $form->category?->name;
        $response->category_slug = $form->category?->slug;
        $response->name = $form->name;
        $response->category_properties = $form->category?->properties;
        $response->locales = $form->locales;

        $propC = new Property();
        $response->available_fields = $propC->getWithCustomCombinations();

        return $response;
    }

    public function readOneCombination($formId, $combinationId)
    {
        return (new Field)->getWithChildren($formId, $combinationId);
    }

    public function readOneJson($formId)
    {
        $form = Form::where('id', $formId)->firstOrFail();
        if ($form->endpoint != 1) {
            abort(403, 'access denied');
        }

        $fields = (new Field)->getWithChildren($formId);

        foreach ($fields as $field) {
            foreach ($field['fields'] as $subfield) {
                // TODO: Move this to the type classes
                // TODO: MOVE THIS CODE!!!!!
                if ($subfield['type'] === 'file' || $subfield['type'] === 'price') {
                    $filetypeId = $subfield['properties']->filetypes;
                    $subfield['properties']->accepts = MimetypeConverter::getMimetype($filetypeId, true);
                }

                if ($subfield['type'] === 'challenges') {
                    $project = Project::where('form_id', $formId)->first();

                    if ($project) {
                        $challenges = ProjectChallenge::where('project_id', $project->strapi_id)->select(['id', 'challenge_name AS nl'])->get();
                        $subfield['properties']->options = new stdClass();
                        $subfield['properties']->options->list = $challenges->toArray();
                    }
                }
            }
        }

        return $fields;
    }

    public function readOneData($id)
    {
        $fields = Field::join('cms_form_properties AS fp', 'field.type', '=', 'fp.type')
            ->where('field.form_id', $id)
            ->where('fp.has_value', 1)
            ->orderBy('field.order')
            ->get(['field.id', 'fields.parent_id', 'fields.properties', 'fields.type']);

        // Convert array so that combinations has the appropriate field as childs
        $returnArray = [];
        foreach ($fields as $field) {
            if (isset($field->label)) {
                $field->label = json_decode($field->label);
            }

            if ($field->parent_id == null) {
                $returnArray[] = $field;
            } else {
                // There is a parent so we need to set it as a child in the field.
                $this->setParentRecursive($returnArray, $field->parent_id, $field);
            }
        }

        return $returnArray;
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'fields' => 'required|array',
        ]);

        $this->updateFieldsCombination($request->id, $request->fields);

        return (new Field)->getWithChildren($request->id);
    }

    private function deleteFieldsRecursive($fieldId)
    {
        $fields = Field::where('parent_id', $fieldId)->get();

        foreach ($fields as $field) {
            $field->delete();

            if ($field->type == 'Combination') {
                $this->deleteFieldsRecursive($field->id);
            }
        }
    }

    private function updateFieldsCombination($formId, $fields, $order = 0, $parentId = null)
    {
        // For loop all the fields that contain in the combination / form
        foreach ($fields as $field) {
            $trigger_field_id = null;
            $trigger_value = '';

            if (isset($field['deleted']) && $field['deleted'] == true) {
                if (isset($field['id'])) {
                    StatusColumn::deleteQuery(Field::where('id', $field['id']), (new Field())->getTable());

                    $this->deleteFieldsRecursive($field['id']);
                }

                continue;
            }

            $newField = (new Field)->updateOrCreate(
                ['id' => $field['id'] ?? 0],
                [
                    'form_id' => $formId,
                    'parent_id' => $parentId,
                    'type' => $field['type'],
                    'properties' => $field['properties'],
                    'order' => $order,
                    'trigger_field_id' => $trigger_field_id, 'trigger_value' => $trigger_value,
                ]
            );

            $order++;

            // If the field is an combination, recursive the function.
            if (strtolower($field['type']) === 'combination') {
                // Recurse the combination.
                if (! isset($field['fields'])) {
                    $field['fields'] = [];
                }

                $this->updateFieldsCombination(
                    $formId,
                    $field['fields'],
                    $order,
                    $newField->id
                );
            }
        }
    }
}
