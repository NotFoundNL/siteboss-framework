<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputVectorImage extends AbstractInput
{
    protected string $type = 'InputVectorImage';

    protected ?object $value = null;

    public function setValue($value): self
    {
        if (! is_object($value)) {
            $this->abortLogSetValueError('LayoutInputVectorImage', 'object', $value);
        }
        $this->value = $value;

        return $this;
    }
}
