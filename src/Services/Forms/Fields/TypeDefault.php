<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeDefault extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        $value = $this->getValue();

        //LOG ERROR
        if (gettype($value) == 'array') {
            return implode(', ', $value);
        }

        if ($value == null) {
            return '';
        }

        return $value;
    }

    public function validate(): bool
    {
        if ($this->isRequired() && $this->emptyValue()) {
            return false;
        }

        return true;
    }
}
