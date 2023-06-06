<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;
use NotFound\Framework\Services\Editor\Repeatable;
use stdClass;

class DropDown extends Properties
{
    public function description(): string
    {
        return 'Simple dropdown';
    }

    public function properties(): void
    {
        $this->overview();
        $this->required();
        $subItems = new Repeatable(new stdClass());
        $subItems->addText('value', 'Value', required: true);
        $subItems->addText('label', 'Label', required: true);
        $this->addRepeatable('items', 'Dimensions', $subItems);

        $this->addText('defaultValue', 'Default value');
    }

    public function serverProperties(): void
    {
    }
}
