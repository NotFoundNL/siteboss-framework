<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Checkbox extends Properties
{
    public function description(): string
    {
        return 'Checkbox';
    }

    public function properties(): void
    {
        $this->overview();
        $this->sortable();
    }

    public function serverProperties(): void {}

    public function checkColumnType(?string $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        if (! in_array($type, ['tinyint', 'int', 'boolean'])) {
            return 'TYPE ERROR: '.$type.' is not a valid type for a text field';
        }

        return '';
    }
}
