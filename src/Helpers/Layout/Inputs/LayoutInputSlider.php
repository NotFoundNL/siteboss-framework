<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputSlider extends AbstractInput
{
    protected string $type = 'InputSlider';

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

    public function setDefaultValue(mixed $defaultValue): self
    {
        if (! is_int($defaultValue)) {
            $this->abortLogSetValueError('InputSlider', 'integer', $defaultValue);
        }
        $this->properties->defaultValue = $defaultValue;

        return $this;
    }
}
