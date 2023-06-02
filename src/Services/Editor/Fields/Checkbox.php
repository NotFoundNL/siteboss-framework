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

    public function serverProperties(): void
    {
    }
}
