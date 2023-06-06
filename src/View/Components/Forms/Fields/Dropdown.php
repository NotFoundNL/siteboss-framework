<?php

namespace App\View\Components\Forms\Fields;

class Dropdown extends AbstractFieldComponent
{
    public function optionList()
    {
        return $this->field->properties->options->list;
    }
}
