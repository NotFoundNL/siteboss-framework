<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeToggle extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        if ($this->emptyValue()) {
            return 'nee';
        }

        return 'ja';
    }

    public function setValueFromPost(): void
    {
        $this->setValue(request($this->id) !== 'false');
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
            'type' => 'toggle',
            'value' => $this->getValue(),
        ];
    }
}
