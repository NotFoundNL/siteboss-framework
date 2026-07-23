<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutBarButton extends AbstractLayout
{
    public function __construct(string $text)
    {
        parent::__construct(type: 'BarButton');

        $this->properties->text = $text;
    }

    public function setIcon(string $icon): self
    {
        $this->properties->icon = $icon;

        return $this;
    }

    public function setSpecialAction(string $action): self
    {
        $this->properties->action = $action;

        return $this;
    }

    public function setLink(string $link): self
    {
        $this->properties->link = $link;

        return $this;
    }
}
