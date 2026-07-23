<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputCheckbox extends AbstractInput
{
    protected string $type = 'InputCheckbox';

    protected ?bool $value = false;

    public function setValue($value): self
    {
        if (! is_bool($value)) {
            $this->abortLogSetValueError('InputCheckbox', 'boolean', $value);
        }
        $this->value = $value;

        return $this;
    }
}
