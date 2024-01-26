<?php

namespace NotFound\Framework\Services\Editor;

use NotFound\Framework\Http\Requests\FormDataRequest;
use NotFound\Layout\Elements\LayoutForm;
use NotFound\Layout\Elements\LayoutText;
use NotFound\Layout\Elements\LayoutTitle;
use NotFound\Layout\Inputs\LayoutInputCheckbox;
use NotFound\Layout\Inputs\LayoutInputDropdown;
use NotFound\Layout\Inputs\LayoutInputNumber;
use NotFound\Layout\Inputs\LayoutInputRepeatable;
use NotFound\Layout\Inputs\LayoutInputTags;
use NotFound\Layout\Inputs\LayoutInputText;
use stdClass;

class FieldsProperties
{
    private $values;

    public function __construct($properties = null)
    {
        $this->values = $properties;
    }

    public function availableFields()
    {
        $fields = ['Text', 'Checkbox', 'ChildTable', 'DropDown', 'Tags', 'Description', 'TableSelect', 'Header', 'DatePicker', 'TimePicker', 'DateTimePicker', 'Image', 'File', 'Filter', 'ContentBlocks', 'Button', 'Slug', 'Number', 'ModelSelect'];
        sort($fields);

        return $fields;
    }

    private function getClass($fieldType)
    {
        $className = '\\NotFound\\Framework\\Services\\Editor\\Fields\\'.$fieldType;

        return new $className($this->values);
    }

    public function getProperties($fieldType)
    {
        $class = $this->getClass($fieldType);

        return $class->getProperties();
    }

    public function getServerProperties($fieldType)
    {
        $class = $this->getClass($fieldType);

        return $class->getServerProperties();
    }

    public function getLayoutFields($fieldType, LayoutForm &$form)
    {
        $form->addTitle(new LayoutTitle('Client properties '));
        $this->addLayoutFields($this->getProperties($fieldType), $form);

        $form->addTitle(new LayoutTitle('Server properties '));
        $this->addLayoutFields($this->getServerProperties($fieldType), $form);
    }

    private function addLayoutFields(array $properties, LayoutForm &$form)
    {
        foreach ($properties as $field) {
            $setDefaults = false;
            if (! isset($this->values->{$field->name})) {
                $setDefaults = true;
            }
            $value = $this->values->{$field->name} ?? null;

            switch ($field->type) {
                case 'Repeatable':

                    $repeatable = new LayoutInputRepeatable($field->name, $field->display_name);
                    $tempForm = new LayoutForm('tempForm');
                    $this->addLayoutFields($field->subItems, $tempForm);
                    $repeatable->setForm($tempForm);
                    $repeatable->setValue($value ?? []);
                    $form->addInput($repeatable);
                    break;
                case 'MultiSelect':
                    $currentValues = [];
                    $selected = $value ?? [];
                    $input = new LayoutInputTags($field->name, $field->display_name);
                    foreach ($field->options as $option) {
                        $input->addOption($option->id, $option->label);
                        if (in_array($option->id, $selected)) {
                            $currentValues[] = $option;
                        }
                    }
                    $input->setValue($currentValues);
                    $form->addInput($input);
                    break;
                case 'Dropdown':
                    $input = new LayoutInputDropdown($field->name, $field->display_name);
                    foreach ($field->options as $option) {
                        $input->addOption($option->value, $option->label);
                    }
                    $input->setValue($value ?? '');
                    $form->addInput($input);
                    break;
                case 'Header':
                    $textField = new LayoutTitle($field->name ?? '');
                    $form->addTitle($textField ?? '');
                    break;
                case 'Text':
                    $textField = new LayoutInputText($field->name, $field->display_name);
                    if (isset($field->required) && $field->required == true) {
                        $textField->setRequired();
                    }
                    if (isset($field->placeholder)) {
                        $textField->setPlaceholder($field->placeholder ?? '');
                    }
                    if ($value == null) {
                        if (isset($field->default) && $field->default != '') {
                            $textField->setValue($field->default);
                        } else {
                            $textField->setValue('');
                        }
                    } else {
                        $textField->setValue($value ?? '');
                    }
                    $form->addInput($textField);
                    break;
                case 'Number':
                    $textField = new LayoutInputNumber($field->name, $field->display_name);
                    if (isset($field->required) && $field->required == true) {
                        $textField->setRequired();
                    }
                    if (isset($field->placeholder)) {
                        $textField->setPlaceholder($field->placeholder ?? '');
                    }
                    if ($value == null) {
                        if (isset($field->default) && $field->default != '') {
                            $textField->setValue($field->default);
                        } else {
                            $textField->setValue('');
                        }
                    } else {
                        $textField->setValue($value ?? '');
                    }
                    $form->addInput($textField);
                    break;
                case 'Checkbox':
                    $checkboxField = new LayoutInputCheckbox($field->name, $field->display_name);
                    if ($setDefaults) {
                        // if (isset($field->default) && $field->default == true) {
                        //     $checkboxField->setValue(true);
                        // } else {
                        $checkboxField->setValue(false);
                    // }
                    } else {
                        $checkboxField->setValue($value ?? false);
                    }
                    $form->addInput($checkboxField);
                    break;
                default:
                    $textField = new LayoutText(
                        'Not implemented type ('.$field->type.') for '.$field->name ?? 'Geen naam voor dit veld'
                    );
                    $form->addText($textField);
            }
        }
    }

    public function updateProperties(string $fieldType, FormDataRequest $request): stdClass
    {
        $customFields = $this->getProperties($fieldType);

        return $this->processUpdateProperties($customFields, $request);
    }

    public function updateServerProperties(string $fieldType, FormDataRequest $request): stdClass
    {
        $customFields = $this->getServerProperties($fieldType);

        return $this->processUpdateProperties($customFields, $request);
    }

    private function processUpdateProperties(array $fields, FormDataRequest $request): stdClass
    {
        $properties = new stdClass();
        foreach ($fields as $field) {
            $data_type = 'string';
            if (in_array($field->type, ['MultiSelect'])) {
                $data_type = 'array';
            } elseif (in_array($field->type, ['Repeatable'])) {
                $data_type = '';
            } elseif (in_array($field->type, ['Checkbox'])) {
                $data_type = 'boolean';
            }
            if (isset($field->required) && $field->required == true) {
                $data_type .= '|required';
            }
            if ($field->type !== 'Header') {
                $request->validate([
                    $field->name => $data_type,
                ]);
                $properties->{$field->name} = $request->{$field->name} ?? null;
            }
        }

        return $properties;
    }
}
