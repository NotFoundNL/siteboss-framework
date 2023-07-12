<?php

namespace NotFound\Framework\Models\Forms;

use Illuminate\Database\Eloquent\SoftDeletes;
use NotFound\Framework\Models\BaseModel;
use NotFound\Framework\Services\Forms\Fields\FactoryType;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputDropdown;
use NotFound\Layout\Inputs\LayoutInputRepeatable;
use NotFound\Layout\Inputs\LayoutInputSlider;
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
                    $textArea->setLocalize();
                    $autoLayoutOptions[] = $textArea->build();
                    break;
                case 'number':
                    $slider = new LayoutInputSlider($option->internal, $option->label);
                    $slider->setMin(1);
                    $slider->setMax(12);
                    $autoLayoutOptions[] = $slider->build();
                    break;
                case 'list':
                    $optionList = new LayoutInputRepeatable($option->internal, $option->label);
                    $optionList->setRequired();
                    $optionList->showDeleted();

                    $form = new \NotFound\Layout\Elements\LayoutForm('form');
                    //                    $form->addInput((new LayoutInputHidden('id')));
                    $form->addInput((new LayoutInputText('option', 'Optie'))->setLocalize()->setRequired());

                    $optionList->setForm($form);
                    $autoLayoutOptions[] = $optionList->build();
                    break;
                case 'optionlist':
                    $optionList = new LayoutInputDropdown($option->internal, $option->label);
                    $optionList->setRequired();
                    foreach ($option->options as $item) {
                        $optionList->addOption($item->value, $item->label);
                    }
                    if (isset($option->required) && $option->required === true) {
                        $optionList->setRequired();
                    }
                    $autoLayoutOptions[] = $optionList->build();
                    break;
                case 'input':
                    $textInput = new LayoutInputText($option->internal, $option->label);
                    if (isset($option->localize) && $option->localize === true) {
                        $textInput->setLocalize();
                    }
                    if (isset($option->required) && $option->required === true) {
                        $textInput->setRequired();
                    }
                    $textInput->setLocalize();
                    $autoLayoutOptions[] = $textInput->build();
                    break;
            }
        }

        return $autoLayoutOptions;
    }
}
