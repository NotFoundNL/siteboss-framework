<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutTabs extends AbstractContainer
{
    public function __construct()
    {
        parent::__construct(type: 'Tabs');
        $this->properties->padding = false;
    }

    public function addPadding(): self
    {
        $this->properties->padding = true;

        return $this;
    }

    public function addTab(LayoutTab $tab): self
    {
        $this->items->add($tab->build());

        return $this;
    }
}
