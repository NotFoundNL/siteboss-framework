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
        $this->sortable();
        $this->localize();
        $this->required();
        $this->addCheckbox('allowEmpty', 'Allow empty', true);
        $this->addText('placeholderText', 'Placeholder text');
    }

    public function serverProperties(): void
    {
        $this->overview();
    }

    protected function rename(): array
    {
        return [
            'allowempty' => 'allowEmpty',
        ];
    }
}
