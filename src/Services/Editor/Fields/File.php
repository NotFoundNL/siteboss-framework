<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class File extends Properties
{
    public function description(): string
    {
        return 'File';
    }

    public function properties(): void
    {
        $this->overview();
        $this->required();
        $this->addText('downloadUrl', 'Download URL (Use [id])', false);
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?string $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        if (! in_array($type, ['varchar'])) {
            return 'TYPE ERROR: '.$type.' is not a valid type for a text field';
        }

        return '';
    }
}
