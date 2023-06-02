<?php

namespace NotFound\Framework\Services\Forms\Fields;

class TypeDate extends AbstractType
{
    public function getReadableValue($langurl): string
    {
        return $this->getValue();
    }

    public function validate(): bool
    {
        if ($this->isRequired() && $this->emptyValue()) {
            return false;
        }

        if (! $this->emptyValue() && ! $this->validateDate($this->getValue())) {
            return false;
        }

        if (! $this->emptyValue() && $this->propertyIsTrue('dob') && ! $this->birthdateValid()) {
            return false;
        }

        return true;
    }

    public function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) === $date;
    }

    private function birthdateValid(): bool
    {
        $datePosted = strtotime($this->getValue());
        $dateNow = time();

        return $datePosted < $dateNow;
    }
}
