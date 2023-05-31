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
        $this->addText('downloadUrl', 'Download URL (Use [id])', false);
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        if (! in_array($type->getName(), ['string'])) {
            return 'TYPE ERROR: '.$type->getName().' is not a valid type for a text field';
        }

        return '';
    }
}
