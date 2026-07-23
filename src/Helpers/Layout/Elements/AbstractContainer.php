<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

use Illuminate\Support\Collection;

/**
 * AbstractLayout
 *
 * Creates a new layout element. Layout elements are all UI
 * elements possible in the Nuxt frontend.
 */
abstract class AbstractContainer extends AbstractLayout
{
    protected Collection $items;

    public function __construct(object $properties = new \stdClass, string $type = '')
    {
        parent::__construct($properties, $type);

        $this->items = new Collection;
    }

    public function build(): object
    {
        return (object) [
            'type' => $this->type,
            'properties' => $this->properties,
            'items' => $this->items,
        ];
    }
}
