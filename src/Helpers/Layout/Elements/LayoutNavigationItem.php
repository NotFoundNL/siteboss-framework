<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

/**
 * LayoutNavigationItem
 *
 * This class is currently only used to test stuff during development and should not be relied upon.
 */
class LayoutNavigationItem extends AbstractLayout
{
    public function __construct(string $title, string $link)
    {
        parent::__construct(type: 'NavigationItem');

        $this->properties->title = $title;
        $this->properties->link = $link;
    }
}
