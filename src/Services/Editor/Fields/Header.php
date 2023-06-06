<?php

namespace NotFound\Framework\Services\Editor\Fields;

use NotFound\Framework\Services\Editor\Properties;

class Header extends Properties
{
    public function description(): string
    {
        return 'Header';
    }

    public function properties(): void
    {
        $this->localize();
    }

    public function serverProperties(): void
    {
    }
}
