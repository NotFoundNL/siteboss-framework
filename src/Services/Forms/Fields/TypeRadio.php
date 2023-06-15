<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeRadio extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        // Return empty string is value is not an array.
        if (! is_array($this->getValue())) {
            $this->setValue(explode(',', $this->getValue()));
        }

        $returnString = [];

        foreach ($this->properties->options->list as $option) {
            // if id is in array value
            if (in_array($option->index, $this->getValue())) {
                if (isset($option->option->$langurl)) {
                    $returnString[] = $option->option->$langurl;
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

        if (! $this->valueExistsInDatabase()) {
            return false;
        }

        return true;
    }

    private function valueExistsInDatabase(): bool
    {
        if ($this->emptyValue()) {
            return true;
        }

        $value = $this->getValue();
        if (is_string($value)) {
            $value = explode(',', $this->getValue());
        }

        // Check if the value exists as an option in the field properties
        foreach ($value as $optionId) {
            $optionIdExist = false;
            foreach ($this->properties->options->list as $option) {
                if ($optionId == $option->index) {
                    $optionIdExist = true;
                }
            }

            if (! $optionIdExist) {
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
            'type2' => 'radio',
            'value' => $this->getValue(),
            'options' => $this->properties->options,
        ];
    }
}
