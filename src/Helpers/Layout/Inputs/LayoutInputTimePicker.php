<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputTimePicker extends AbstractInput
{
    protected string $type = 'InputTimePicker';

    protected string $value = '';

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }
}
