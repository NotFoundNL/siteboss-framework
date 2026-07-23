<?php

namespace NotFound\Framework\Helpers\Layout\Inputs;

use Illuminate\Support\Facades\Log;

class LayoutInputTags extends AbstractInput
{
    protected string $type = 'InputTags';

    protected array $value = [];

    /**
     * Set the current value of the input select
     * The value must be an array of identifiers, most likely ids
     *
     * @param  int[]  $value  array of identifiers
     * @return void
     */
    public function setValue(mixed $value): self
    {
        if (! is_array($value)) {
            $this->abortLogSetValueError('InputTags', 'array', $value);
        }
        $this->value = $value;

        return $this;
    }

    public function setEndpoint($url)
    {
        if (isset($this->properties->items) && count($this->properties->items) > 0) {
            Log::error('Cannot use endpoint when using options.');
        }
        $this->properties->endPoint = $url;
    }

    public function addOption($value, $readableValue = null): self
    {
        if (isset($this->properties->endPoint)) {
            Log::error('Cannot add items to Tags when using ajax.');
        }
        $this->properties->items[] = [
            'id' => $value,
            'label' => $readableValue ?? $value,
        ];

        return $this;
    }

    public function addItem($value, $readableValue = null): self
    {
        trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);

        return $this->addOption($value, $readableValue);
    }
}
