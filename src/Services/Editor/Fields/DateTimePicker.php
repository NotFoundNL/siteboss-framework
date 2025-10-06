<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class DateTimePicker extends Properties
{
    public function description(): string
    {
        return 'DateTimePicker';
    }

    public function properties(): void
    {
        $this->overview();
        $this->sortable();
        $this->required();
    }

    public function serverProperties(): void {}

    public function checkColumnType(?string $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }
        if (! in_array($type, ['datetime'])) {
            return 'TYPE ERROR: '.$type.' is not a valid type for a date field';
        }

        return '';
    }
}
