<?php

namespace NotFound\Framework\Models\Forms;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Models\BaseModel;

/**
 * NotFound\Framework\Models\Forms\Field
 *
 * @property int $id
 * @property int $form_id
 * @property int|null $parent_id
 * @property string $type
 * @property string|null $label
 * @property object|null $properties
 * @property int $order
 * @property int|null $trigger_field_id
 * @property string|null $trigger_value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \NotFound\Framework\Models\Forms\Property|null $property
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Field newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Field newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Field onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Field query()
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereTriggerFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereTriggerValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Field withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Field withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Field extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form_fields';

    protected $visible = [
        'id',
        'type',
        'properties',
        'category',
        'form_id',
        'parent_id',
    ];

    protected $casts = [
        'properties' => 'object',
    ];

    protected $guarded = [];

    public function getBladeComponent()
    {
        return 'fields::'.$this->type;
    }

    public function property()
    {
        return $this->belongsTo(Property::class, 'type', 'type');
    }

    /**
     * Get all the fields from the $oldFormId and clone them in the new form.
     * This includes cloning a combination with a new id and such.
     *
     * @param  Form  $newForm  The form that this fields needs to be a child of
     * @param mixed oldFormId
     */
    public function cloneToNewForm(Form $newForm, Form $oldForm)
    {
        $fields = $this->where('form_id', $oldForm->id)->get();

        // We need to loop twice, since we're not sure if the combination are first
        $combinationIdToNewId = [];
        foreach ($fields as $field) {
            if (strtolower($field->type) == 'combination') {
                $parent_id = null;
                if ($field->parent_id != null) {
                    $parent_id = $combinationIdToNewId[$field->parent_id];
                }

                // We clone the combinations and add them to the db
                $newCombination = $field->replicate();
                $newCombination->form_id = $newForm->id;
                $newCombination->parent_id = $parent_id;
                $newCombination->save();

                // We save an assoc array with the old parent_id's and the new.
                $combinationIdToNewId[$field->id] = $newCombination->id;
            }
        }

        foreach ($fields as $field) {
            if (strtolower($field->type) == 'combination') {
                continue;
            }

            // We use the assoc array to determine which parent_id is the new one;
            $newParentId = null;
            if ($field->parent_id != null) {
                $newParentId = $combinationIdToNewId[$field->parent_id];
            }

            $newField = $field->replicate();
            $newField->form_id = $newForm->id;
            $newField->parent_id = $newParentId;
            $newField->save();
        }
    }

    /**
     * Gets all the fields by formid then convert this into an actual
     * hierarchy where the fields that have a parent are children.
     *
     * also able to set combinationId
     *
     * @param  mixed  $combinationId  set if you only want to get from 1 combination
     */
    public function getWithChildren(int $formId, ?int $combinationId = null)
    {
        // TODO: combine with GetChildrenOfCombination()
        $fieldQuery = $this
            ->where('form_id', $formId);

        if ($combinationId) {
            $fieldQuery = $fieldQuery->where('parent_id', $combinationId);
        }

        $fields = $fieldQuery
            ->orderBy('order')
            ->get()
            ->toArray();

        $returnArray = [];
        foreach ($fields as $field) {
            if (strtolower($field['type']) == 'combination') {
                $field['fields'] = [];
            }

            if ($field['parent_id'] == null) {
                $returnArray[] = $field;
            } else {
                // There is a parent so we need to set it as a child in the field.
                $this->setParentRecursive($returnArray, $field['parent_id'], $field);
            }
        }

        return $returnArray;
    }

    public function GetChildrenOfCombination(int $formId, int $combinationId): Collection
    {
        $fieldQuery = $this
            ->where('form_id', $formId);

        if ($combinationId) {
            $fieldQuery = $fieldQuery->where('parent_id', $combinationId);
        }

        return $fieldQuery
            ->orderBy('order')
            ->get();
    }

    private function setParentRecursive(&$fields, $parentId, $fieldToSet)
    {
        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]['id'] == $parentId) {
                $fields[$i]['fields'][] = $fieldToSet;
            } elseif (strtolower($fields[$i]['type']) == 'combination') {
                $this->setParentRecursive($fields[$i]['fields'], $parentId, $fieldToSet);
            }
        }
    }
}
