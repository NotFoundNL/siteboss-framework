<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Slug extends Properties
{
    public function description(): string
    {
        return 'Slug';
    }

    public function properties(): void
    {
        $this->allOverviewOptions();
        $this->localize();
        $this->required();
    }

    public function serverProperties(): void
    {
        $this->addText('source', 'Source field internal name', false);
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }
        if (! in_array($type->getName(), ['string'])) {
            return 'TYPE ERROR: '.$type->getName().' is not a valid type for a slug field';
        }

        return '';
    }
}
