<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;
use NotFound\Framework\Services\Editor\Repeatable;
use stdClass;

class Image extends Properties
{
    public function description(): string
    {
        return 'Image';
    }

    public function properties(): void
    {
        $this->overview();
        $this->required();
        $subItems = new Repeatable(new stdClass());
        $subItems->addText('filename', 'Filename', required: true);
        $subItems->addText('width', 'Width', required: true);
        $subItems->addText('height', 'Height', required: true);
        $this->addRepeatable('sizes', 'Dimensions', $subItems);

        $options = [];
        $options[] = (object) ['value' => 'constrain', 'label' => 'Crop to dimensions'];
        $options[] = (object) ['value' => 'fitWithin', 'label' => 'Fit within dimensions'];
        $this->addDropdown('cropType', 'Crop type', $options);
    }

    public function serverProperties(): void
    {
        $this->addCheckbox('createPNG', 'Create PNG');
    }
}
