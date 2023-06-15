<?php

namespace NotFound\Framework\View\Components\Forms\Fields;

class Checkbox extends AbstractFieldComponent
{
    public function optionList()
    {
        return array_filter($this->field->properties->options->list, fn ($option) => ! $option->deleted);
    }
}
