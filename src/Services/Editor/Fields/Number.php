<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Number extends Properties
{
    public function description(): string
    {
        return 'Number';
    }

    public function properties(): void
    {
        $this->allOverviewOptions();
        $this->localize();
        $this->required();
        $this->addCheckbox('disabled', 'Disable editing');
    }

    public function serverProperties(): void
    {
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        return '';
    }
}
