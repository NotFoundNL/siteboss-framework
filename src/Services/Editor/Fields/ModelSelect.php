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
        $this->allOverviewOptions();

        $this->addText('selectedModel', 'Custom model path', true, default: 'App\\Models\\');
    }

    public function serverProperties(): void
    {
        $this->addText('methodName', 'Method', true);
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
