<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeEmail extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        return $this->getValue();
    }

    public function validate(): bool
    {
        if (! $this->emptyValue() && $this->invalidEmail()) {
            return false;
        }

        return true;
    }

    public function fillImportantData(\stdclass $formAttributes)
    {
        if ($this->getType() == 'email' && $this->propertyIsTrue('primary')) {
            $formAttributes['primary_email'] = $this->getValue();
        }
    }

    private function invalidEmail(): bool
    {
        if (! filter_var($this->getValue(), FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    public function isEditable(): object
    {
        return (object) [
            'edit' => true,
            'type' => 'string',
        ];
    }
}
