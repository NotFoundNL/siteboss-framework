<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Description extends Properties
{
    public function description(): string
    {
        return 'Description';
    }

    public function properties(): void
    {
        $this->addText('desc', 'Description (HTML allowed, no other fields here will be shown)');
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?string $type): string
    {

        return '';
    }
}
