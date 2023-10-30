<?php

namespace NotFound\Framework\Services\Editor\Fields;

use Doctrine\DBAL\Types\Type;
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

        $options = [];
        $options[] = (object) ['value' => 'constrain', 'label' => 'Cover (crop middle of image to fit exact dimensions)'];
        $options[] = (object) ['value' => 'fitWithin', 'label' => 'Fit (resize image proportionally to fit within dimensions)'];
        $subItems->addDropdown('cropType', 'Crop type', $options);

        $this->addRepeatable('sizes', 'Dimensions', $subItems);
    }

    public function serverProperties(): void
    {
        $this->addCheckbox('createPNG', 'Create PNG');
    }

    public function checkColumnType(?Type $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        return '';
    }
}
