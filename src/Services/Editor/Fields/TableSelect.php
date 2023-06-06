<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class TableSelect extends Properties
{
    public function description(): string
    {
        return 'Table dropdown';
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
        $this->addText('foreignKey', 'Foreign key', required: true, default: 'id');
        $this->addText('foreignDisplay', 'Foreign display', true, default: 'name/title');
        $this->addCheckbox('useStatus', 'Use status column', true);
        $this->addCheckbox('useOrder', 'Use order column', true);
        $this->addText('customQuery', 'Custom query (not implemented)');
        $this->addCheckbox('searchForItem', 'Search within results', true);
    }

    protected function rename(): array
    {
        return [
            'table' => 'foreignTable',
            'foreignkey' => 'foreignKey',
            'foreigndisplay' => 'foreignDisplay',
            'customquery' => 'customQuery',
        ];
    }
}