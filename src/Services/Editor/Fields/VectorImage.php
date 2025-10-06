<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class VectorImage extends Properties
{
    public function description(): string
    {
        return 'VectorImage';
    }

    public function properties(): void
    {
        $this->overview();
        $this->required();
        $this->addCheckbox('darkBackground', 'Dark background');
        $this->addCheckbox('lightBackground', 'Light background');
    }

    public function serverProperties(): void {}

    public function checkColumnType(?string $type): string
    {
        if ($type === null) {
            return 'COLUMN MISSING';
        }

        return '';
    }
}
