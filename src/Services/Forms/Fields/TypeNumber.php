<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeNumber extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        return $this->getValue();
    }

    public function validate(): bool
    {
        if ($this->isRequired() && $this->emptyValue()) {
            return false;
        }

        if (! $this->isInt()) {
            return false;
        }

        return true;
    }

    private function isInt(): bool
    {
        if ($this->emptyValue()) {
            return true;
        }

        return is_numeric($this->getValue());
    }

    public function isEditable(): object
    {
        return (object) [
            'edit' => true,
            'type' => 'string',
        ];
    }
}
