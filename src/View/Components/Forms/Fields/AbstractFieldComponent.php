<?php

namespace App\View\Components\Forms\Fields;

use Illuminate\View\Component;
use NotFound\Framework\Models\Forms\Field;

abstract class AbstractFieldComponent extends Component
{
    public function __construct(
        public Field $field,
    ) {
    }

    public function render()
    {
        if (view()->exists('forms.fields.'.$this->field->type)) {
            return 'forms.fields.'.$this->field->type;
        }

        return view('siteboss.forms.fields.'.$this->field->type);
    }

    public function colClasses(): string
    {
        $p = $this->field->properties;

        $columnsClasses = '';
        if (isset($p?->mobilewidth)) {
            $columnsClasses .= 'col-xs-'.$p->mobilewidth;
        }

        if (isset($p?->width)) {
            $columnsClasses .= ' col-md-'.$p->width;
        }

        if ($columnsClasses === '') {
            $columnsClasses = 'col-xs-12';
        }

        return $columnsClasses;
    }

    public function id(): string
    {
        return $this->field->id ?? '';
    }

    public function label(): string
    {
        $label = $this->property('label');

        return $this->getByLanguage($label);
    }

    public function placeholder(): string
    {
        $placeholder = $this->property('placeholder');

        return $this->getByLanguage($placeholder);
    }

    public function required(): string
    {
        $required = $this->property('required');
        if ($required == true) {
            return 'required';
        }

        return '';
    }

    public function getByLanguage(?\stdClass $object): string
    {
        $locale = app()->getLocale();
        if (isset($object?->$locale)) {
            return $object->$locale;
        }
        $fallbackLocale = app()->getFallbackLocale();

        return $object->$fallbackLocale ?? '';
    }

    public function property(string $key)
    {
        return $this->field->properties?->$key ?? null;
    }
}
