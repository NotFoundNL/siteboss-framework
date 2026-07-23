<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

/**
 * LayoutPage
 *
 * The page is the root element for most API calls. Do not embed a LayoutPage in other UI elements (except for containers that have no UI)
 */
class LayoutNavbar extends AbstractLayout
{
    public function __construct(string $title)
    {
        parent::__construct(type: 'Navbar');

        $this->properties->title = $title;
    }

    public function build(): object
    {
        return (object) [
            'type' => $this->type,
            'items' => $this->items,
            'properties' => $this->properties,
        ];
    }
}
