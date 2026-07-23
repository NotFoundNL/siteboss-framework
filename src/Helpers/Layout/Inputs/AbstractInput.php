<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

use Illuminate\Support\Facades\Log;
use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;

abstract class AbstractInput extends AbstractLayout
{
    public function __construct(
        protected string $internal,
        protected string $label = '',
    ) {
        parent::__construct(type: $this->type);
    }

    public function setDescription($description): self
    {
        $this->properties->description = $description;

        return $this;
    }

    public function setRequired(bool $required = true): self
    {
        $this->properties->required = $required;

        return $this;
    }

    public function setDisabled(bool $disabled = true): self
    {
        $this->properties->disabled = $disabled;

        return $this;
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->properties->placeholder = $placeholder;

        return $this;
    }

    public function setValue(mixed $value): self
    {
        if (! is_string($value)) {
            $this->abortLogSetValueError('AbstractInput', 'string', $value);
        }
        $this->value = $value;

        return $this;
    }

    public function setDefaultValue(mixed $defaultValue): self
    {
        if (! is_string($defaultValue)) {
            $this->abortLogSetValueError('AbstractInput', 'string', $defaultValue);
        }
        $this->properties->defaultValue = $defaultValue;

        return $this;
    }

    protected function abortLogSetValueError(string $class, string $requiredType, mixed $newValue): void
    {
        $message = sprintf(
            "[%s] setValue argument must be of type: '%s'. Value given:'%s'",
            $class,
            $requiredType,
            json_encode($newValue)
        );

        Log::error(
            $message,
            [
                'internal' => $this->internal,
                'type' => $this->type,
            ]
        );

        abort(422, 'invalidValue');
    }

    final public function build(): object
    {
        $this->properties->title = $this->label;

        return (object) [
            'type' => $this->type,
            'internal' => $this->internal,
            'properties' => $this->properties,
            'data' => [
                'value' => $this->value,
            ],
        ];
    }
}
