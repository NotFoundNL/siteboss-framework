<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutBar extends AbstractContainer
{
    public function __construct()
    {
        parent::__construct(type: 'Bar');
    }

    public function removePadding(): self
    {
        $this->properties->removePadding = true;

        return $this;
    }

    public function noBackground(): self
    {
        $this->properties->noBackground = true;

        return $this;
    }

    public function addBarButton(LayoutBarButton $btn): self
    {
        $this->items->add($btn->build());

        return $this;
    }

    public function addPager(LayoutPager $pager): self
    {
        $this->items->add($pager->build());

        return $this;
    }

    public function addSearchBox(LayoutSearchBox $searchBox): self
    {
        $this->items->add($searchBox->build());

        return $this;
    }

    public function addText(LayoutText $text): self
    {
        $this->items->add($text->build());

        return $this;
    }
}
