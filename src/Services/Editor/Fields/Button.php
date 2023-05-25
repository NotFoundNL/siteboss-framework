<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Button extends Properties
{
    public function description(): string
    {
        return 'Button';
    }

    public function properties(): void
    {
        $this->overview();
        $this->sortable();
        $this->addText('link', 'Link use {id} as RecordId', '/app/site/');
        $this->addCheckbox('external', 'External link', false);
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        return '';
    }
}
