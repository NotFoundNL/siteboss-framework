<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Link extends Properties
{
    public function description(): string
    {
        return 'Link';
    }

    public function properties(): void
    {

    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?string $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        return '';
    }
}
