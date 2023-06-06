<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class File extends Properties
{
    public function description(): string
    {
        return 'File';
    }

    public function properties(): void
    {
        $this->overview();
        $this->addText('downloadUrl', 'Download URL (Use [id])', false);
    }

    public function serverProperties(): void
    {
    }
}
