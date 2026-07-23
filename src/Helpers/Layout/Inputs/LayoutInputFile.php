<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputFile extends AbstractInput
{
    protected string $type = 'InputFile';

    protected ?object $value = null;

    public function setValue($value): self
    {
        if (! is_object($value)) {
            $this->abortLogSetValueError('LayoutInputImage', 'object', $value);
        }
        $this->value = $value;

        return $this;
    }
}
