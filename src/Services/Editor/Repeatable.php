<?php

namespace NotFound\Framework\Services\Editor;

class Repeatable extends Properties
{
    public function description(): string
    {
        return 'Repeatable';
    }

    public function properties(): void
    {
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?string $type): string
    {
        trigger_error('This should never be called', E_USER_ERROR);

        return '';
    }
}
