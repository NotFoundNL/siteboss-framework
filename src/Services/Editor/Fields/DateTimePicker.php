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

    public function serverProperties(): void
    {
    }
}
