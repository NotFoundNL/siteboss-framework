<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutTab extends AbstractLayout
{
    /**
     * @param  bool|null  $active  When null, the frontend router will figure it out.
     */
    public function __construct(string $title, string $link, ?bool $active = null)
    {
        parent::__construct(type: 'Tab');

        $this->properties->title = $title;
        $this->properties->link = $link;
        $this->properties->active = $active;
    }
}
