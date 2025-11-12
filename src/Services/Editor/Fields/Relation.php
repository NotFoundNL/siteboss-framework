<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Relation extends Properties
{
    public function description(): string
    {
        return 'Relation';
    }

    public function properties(): void {}

    public function serverProperties(): void
    {
        $this->addText('relation', 'Relation name in Laravel, the child table needs to be defined here too', true);
    }

    public function checkColumnType(?string $type): string
    {
        if ($type !== null) {
            return 'COLUMN NOT NEEDED';
        }

        return '';
    }
}
