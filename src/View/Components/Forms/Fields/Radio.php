<?php

namespace App\View\Components\Forms\Fields;

class Radio extends AbstractFieldComponent
{
    public function optionList()
    {
        return $this->field->properties->options->list;
    }
}
