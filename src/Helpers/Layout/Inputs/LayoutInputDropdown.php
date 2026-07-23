<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

class LayoutInputDropdown extends AbstractInput
{
    protected string $type = 'InputDropdown';

    protected ?string $value = '';

    public function setValue(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function setSearchable(): self
    {
        $this->properties->searchable = true;

        return $this;
    }

    public function addItem($value, $readableValue = null): self
    {
        trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);

        return $this->addOption($value, $readableValue);
    }

    public function addOption($value, $readableValue = null): self
    {
        $this->properties->items[] = ['id' => $value, 'name' => $readableValue ?? $value];

        return $this;
    }
}
