<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutPager extends AbstractLayout
{
    public function __construct(int $totalItems, ?int $itemsPerPage = null)
    {
        parent::__construct(type: 'Pager');

        $this->properties->itemsPerPage = $itemsPerPage ?? 25;
        $this->properties->totalItems = $totalItems;
    }
}
