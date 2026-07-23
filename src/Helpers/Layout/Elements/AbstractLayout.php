<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

/**
 * Creates a new layout element. Layout elements are all UI
 * elements possible in the Nuxt frontend.
 */
abstract class AbstractLayout
{
    public function __construct(
        public object $properties = new \stdClass,
        protected string $type = '',
    ) {}

    public function type(): string
    {
        return $this->type;
    }

    /**
     * setLocalize
     *
     * This option will mark the element as translatable.
     * Currently this will not affect the frontend at all.
     */
    public function setLocalize(): self
    {
        $this->properties->localize = true;

        return $this;
    }

    public function build(): object
    {
        return (object) [
            'type' => $this->type,
            'properties' => $this->properties,
        ];
    }
}
