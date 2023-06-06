<?php

namespace App\View\Components\Forms\Fields;

class File extends AbstractFieldComponent
{
    public function filetypes(): string
    {
        if ($filetypes = $this->property('filetypes')) {
            return sprintf('accept="%s"', $filetypes);
        }

        return '';
    }

    public function multiple(): string
    {
        if ($this->property('multiple') != null) {
            return 'multiple';
        }

        return '';
    }
}
