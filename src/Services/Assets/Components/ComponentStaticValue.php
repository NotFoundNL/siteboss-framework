<?php

namespace NotFound\Framework\Services\Assets\Components;

use NotFound\Layout\Elements\AbstractLayout;

/**
 * ComponentStaticValue
 *
 * This component can be used to insert a static value into
 * a tableservice. This is used by the ComponentChildTable.
 */
class ComponentStaticValue extends AbstractComponent
{
    private mixed $staticValue;

    public function getAutoLayoutClass(): ?AbstractLayout
    {
        return null;
    }

    public function validate($newValue): bool
    {
        return true;
    }

    public function setStaticValue($value): void
    {
        $this->staticValue = $value;
    }

    public function getValueForStorage(): ?string
    {
        return $this->staticValue;
    }
}
