<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutButton extends AbstractLayout
{
    public function __construct(string $title)
    {
        parent::__construct(type: 'Button');

        $this->properties->title = $title;
    }

    public function setSticky(): self
    {
        $this->properties->sticky = true;

        return $this;
    }

    public function addAlternative(string $internal, string $display_name): self
    {
        if (! isset($this->properties->alternatives)) {
            $this->properties->alternatives = [];
        }
        $this->properties->alternatives[$internal] = $display_name;

        return $this;
    }
}
