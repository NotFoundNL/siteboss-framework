<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Filter extends Properties
{
    public function description(): string
    {
        return '';
    }

    public function properties(): void
    {
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?string $type): string
    {
        return '';
    }
}
