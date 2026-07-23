<?php

namespace NotFound\Framework\Helpers\Layout\Elements\Table;

/**
 * LayoutTableRow
 *
 * Created a new row UI element for tables. Can only be added to LayoutTable.
 */
class LayoutTableRow
{
    public array $columns = [];

    public function __construct(
        public int $id,
        public string $link = '',
        public object $properties = new \stdClass,
    ) {
        return $this;
    }

    public function addColumn(LayoutTableColumn $column)
    {
        $this->columns[] = $column;

        return $this;
    }
}
