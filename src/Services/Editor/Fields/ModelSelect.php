<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class ModelSelect extends Properties
{
    public function description(): string
    {
        return 'Model dropdown';
    }

    public function properties(): void
    {
        $this->overview();
        $this->sortable();
        $this->required();
    }

    public function serverProperties(): void
    {
        $this->addText('foreignTable', 'Foreign table', true);
        $this->addText('foreignDisplay', 'Function', true, default: 'cmsDisplay');
    }

    protected function rename(): array
    {
        return [
            'table' => 'foreignTable',
            'foreignkey' => 'foreignKey',
            'foreigndisplay' => 'foreignDisplay',
        ];
    }

    public function checkColumnType(?\Doctrine\DBAL\Types\Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }
        if (! in_array($type->getName(), ['string', 'integer'])) {
            return 'TYPE ERROR: '.$type->getName().' is not a valid type for a table select field';
        }

        return '';
    }
}
