<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeDropdown extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        if ($this->emptyValue()) {
            return '';
        }

        $returnString = [];

        foreach ($this->properties->options->list as $option) {
            // if id is in array value

            // For legacy support we also check for the id,
            // This will be removed in the future
            if (isset($option->index)) {
                if ($option->index != $this->getValue()) {
                    continue;
                } elseif ($option->id != $this->getValue()) {
                    continue;
                }
            }
            if (isset($option->option->$langurl)) {
                $returnString[] = $option->option->$langurl;
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

        // Check if the value exists as an option in the field properties
        foreach ($this->properties->options->list as $option) {
            if (isset($option->index) && $this->getValue() == $option->index) {
                return true;
            }

            if (isset($option->id) && $this->getValue() == $option->id) {
                return true;
            }

        }

        return false;
    }

    public function isEditable(): object
    {
        return (object) [
            'edit' => true,
            'type' => 'dropdown',
            'value' => $this->getValue(),
            'options' => $this->properties->options,
        ];
    }
}
