<?php

namespace NotFound\Framework\Models\Forms;

use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Models\BaseModel;
use NotFound\Framework\Services\Forms\Fields\FactoryType;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputText;
use NotFound\Layout\Inputs\LayoutInputTextArea;

/**
 * NotFound\Framework\Models\Forms\Property
 *
 * @property int $id
 * @property int $custom
 * @property string $name
 * @property string $type
 * @property object|null $options
 * @property int|null $combinationId
 * @property string|null $has_value
 * @property \Illuminate\Support\Carbon|null $deleted_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Property newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Property newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Property onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Property query()
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereCombinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereHasValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Property withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Property withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Property extends BaseModel
{
    use SoftDeletes;

    protected $table = 'cms_form_properties';

    protected $visible = [
        'id',
        'custom',
        'name',
        'type',
        'options',
        'has_value',
        'combinationId',
    ];

    protected $casts = [
        'options' => 'object',
    ];

    public function getWithCustomCombinations()
    {
        $typeFactory = new FactoryType();
        $properties = [];
        foreach ($this->get() as $property) {
            $type = $typeFactory->getByType($property->type, $property->options, 4);
            $property->{'options'} = $this->makeAutoLayout($type->getOptions($property->options));

            $properties[] = $property;
        }

        // Add the combinations
        $customProperties = Form::whereType('Combination')->where('archived', 0)->get(['id', 'name', 'type']);
        foreach ($customProperties as $property) {
            /** @var Form $property */

            // Converted to array otherwise the visible properties won't
            // allow for customer variables
            $customProperty = $property->toArray();
            $customProperty['custom'] = 1;
            $customProperty['type'] = $property->name;
            $customProperty['combinationId'] = $property->id;

            $properties[] = $customProperty;
        }

        return $properties;
    }

    private function makeAutoLayout($options)
    {
        $autoLayoutOptions = [];
        foreach ($options as $option) {
            switch ($option->type) {
                case 'checkbox':
                    $checkbox = new LayoutInputCheckbox($option->internal, $option->label);
                    $autoLayoutOptions[] = $checkbox->build();
                    break;
                case 'textarea':
                    $textArea = new LayoutInputTextArea('textarea'.$option->internal, $option->label);
                    $autoLayoutOptions[] = $textArea->build();
                    break;
                case 'number':
                    $number = new LayoutInputText($option->internal, $option->label);
                    $autoLayoutOptions[] = $number->build();
                    break;
                case 'list':
                    $list = new LayoutInputText('list'.$option->internal, $option->label);
                    $autoLayoutOptions[] = $list->build();
                    break;
                case 'optionlist':
                    $optionList = new LayoutInputText('optionlist'.$option->internal, $option->label);
                    $autoLayoutOptions[] = $optionList->build();
                    break;
                case 'input':
                    $textInput = new LayoutInputText($option->internal, $option->label);
                    $autoLayoutOptions[] = $textInput->build();
                    break;
            }
        }

        return $autoLayoutOptions;
    }
}
