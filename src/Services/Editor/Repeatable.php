<?php

namespace NotFound\Framework\Services\Editor;

use Doctrine\DBAL\Types\Type;

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

    public function checkColumnType(?Type $type): string
    {
        trigger_error('This should never be called', E_USER_ERROR);

        return '';
    }
}
