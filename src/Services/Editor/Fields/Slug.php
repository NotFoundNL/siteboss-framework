<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Slug extends Properties
{
    public function description(): string
    {
        return 'Slug';
    }

    public function properties(): void
    {
        $this->allOverviewOptions();
        $this->localize();
        $this->required();
    }

    public function serverProperties(): void
    {
        $this->addText('source', 'Source field internal name', false);
    }
}
