<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Tags extends Properties
{
    public function description(): string
    {
        return 'Tags';
    }

    public function properties(): void
    {
        $this->overview();
        $this->addCheckbox('foreignLocalize', 'Localize foreign table');
        $this->addCheckbox('keepRows', 'Keep rows');
        $this->addTitle('Advanced options');

        $this->addCheckbox('tagsSortable', 'Tags are sortable');
        $this->addCheckbox('lazyLoad', 'Fetch values via ajax (recommended for large tables)');
    }

    public function serverProperties(): void
    {
        $this->addText('linkTable', 'Koppeltabel', true);
        $this->addText('linkItemId', 'Link item id', true);
        $this->addText('linkTagId', 'Link tag id', true);

        $this->addTitle('Foreign table');
        $this->addText('foreignTable', 'foreignTable');
        $this->addText('foreignTagId', 'foreignTagId');
        $this->addText('foreignDisplayColumn', 'foreignDisplayColumn');
        $this->addCheckbox('useStatus', 'Table contains status column', true);
    }

    protected function rename(): array
    {
        return [
            'ajaxtags' => 'lazyLoad',
        ];
    }
}
