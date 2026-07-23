<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class InputSettings
{
    public static function getTypes(): array
    {
        return [
            'checkbox' => 'Checkbox',
            'text' => 'Text',
            'email' => 'Email',
        ];
    }
}
