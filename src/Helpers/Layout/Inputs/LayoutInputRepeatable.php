<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

use NotFound\Framework\Helpers\Layout\Elements\LayoutForm;

class LayoutInputRepeatable extends AbstractInput
{
    protected string $type = 'InputRepeatable';

    protected mixed $value = null;

    protected $inputs = [];

    public function setForm(LayoutForm $form)
    {
        $this->properties->template = $form->build()->items;
    }

    public function showDeleted(): self
    {
        $this->properties->showDeleted = true;

        return $this;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }
}
