<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class DatePicker extends Properties
{
    public function description(): string
    {
        return 'DatePicker';
    }

    public function properties(): void
    {
        $this->overview();
        $this->sortable();
        $this->localize();
        $this->required();
        $this->addCheckbox('allowEmpty', 'Allow empty', true);
        $this->addCheckbox('datetimeFormat', 'Use datetime format', true);
        $this->addText('placeholderText', 'Placeholder text');
    }

    public function serverProperties(): void
    {
    }

    protected function rename(): array
    {
        return [
            'allowempty' => 'allowEmpty',
        ];
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }
        if ($type->getName() === 'int') {
            return 'TYPE WARNING: '.$type->getName().' should be converted to datetime';
        }

        if ($type->getName() !== 'datetime') {
            return 'TYPE ERROR: '.$type->getName().' is not a valid type for a date field';
        }

        return '';
    }
}
