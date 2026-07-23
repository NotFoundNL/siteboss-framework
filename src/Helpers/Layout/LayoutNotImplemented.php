<?php

namespace NotFound\Framework\Helpers\Layout\Helpers;

use NotFound\Framework\Helpers\Layout\Inputs\AbstractInput;

class LayoutNotImplemented extends AbstractInput
{
    protected string $type = 'NotImplemented';

    protected string $value = '';

    protected bool $useDefaultStorageMechanism = false;

    public function setOriginalType(string $type): self
    {
        $this->properties->type = $type;

        return $this;
    }
}
