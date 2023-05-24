<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeCheckbox extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        if ($this->emptyValue()) {
            return '';
        }

        // Return empty string is value is not an array.
        if (! is_array($this->getValue())) {
            return '';
        }

        $returnString = [];

        foreach ($this->properties->options->list as $option) {
            // if id is in array value
            if (in_array($option->index, $this->getValue())) {
                if (isset($option->$langurl)) {
                    $returnString[] = $option->$langurl;
                }
            }
        }

        return implode(', ', $returnString);
    }

    public function validate(): bool
    {
        if ($this->isRequired() && $this->emptyValue()) {
            return false;
        }

        if (! $this->emptyValue() && ! $this->valueExistsInDatabase()) {
            return false;
        }

        return true;
    }

    private function valueExistsInDatabase(): bool
    {
        // Check if the value exists as an option in the field properties
        foreach ($this->getValue() as $optionId) {
            $optionIdExists = false;
            foreach ($this->properties->options->list as $option) {
                if ($optionId == $option->index) {
                    $optionIdExists = true;
                }
            }

            if (! $optionIdExists) {
                return false;
            }
        }

        return true;
    }

    public function isEditable(): object
    {
        return (object) [
            'edit' => true,
            'type' => 'list',
            'type2' => 'checkbox',
            'value' => $this->getValue(),
            'options' => $this->properties->options,
        ];
    }
}
