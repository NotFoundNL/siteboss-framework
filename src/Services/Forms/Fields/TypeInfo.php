<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeInfo extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        return '';
    }

    public function validate(): bool
    {
        return true;
    }
}
