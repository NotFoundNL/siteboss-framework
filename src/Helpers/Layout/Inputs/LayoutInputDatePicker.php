<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputDatePicker extends AbstractInput
{
    protected string $type = 'InputDatePicker';

    protected string $value = '';

    public function setValue(mixed $value): self
    {
        $this->value = $value ?? '';

        return $this;
    }
}
