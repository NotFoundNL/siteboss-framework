<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputNumber extends AbstractInput
{
    protected string $type = 'InputNumber';

    protected string $value = '';

    public function setMin(int $min): self
    {
        $this->properties->minValue = $min;

        return $this;
    }

    public function setMax(int $max): self
    {
        $this->properties->maxValue = $max;

        return $this;
    }
}
