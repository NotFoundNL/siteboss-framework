<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeTextarea extends AbstractType
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

        return true;
    }

    public function isEditable(): object
    {
        return (object) [
            'edit' => true,
            'type' => 'string',
        ];
    }
}
