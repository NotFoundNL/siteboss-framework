<?php

namespace NotFound\Framework\Helpers\Layout\Elements\Table;

/**
 * LayoutTableHeader
 *
 * Created a new header UI element for tables. Can only be added to LayoutTable.
 */
class LayoutTableHeader
{
    public function __construct(
        public string $title,
        public string $internal,
        public string $type = 'text',
        public object $properties = new \stdClass,

    ) {
        return $this;
    }

    public function sortable(): LayoutTableHeader
    {
        $this->properties->sortable = true;

        return $this;
    }
}
