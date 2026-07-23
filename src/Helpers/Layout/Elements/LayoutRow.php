<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutRow extends AbstractContainer
{
    public function __construct()
    {
        parent::__construct(type: 'Row');
    }

    public function addWidget(LayoutWidget $widget): self
    {
        $this->items->add($widget->build());

        return $this;
    }

    public function addForm(LayoutForm $form): self
    {
        $this->items->add($form->build());

        return $this;
    }

    public function addButton(LayoutButton $layoutButton): self
    {
        $this->items->add($layoutButton->build());

        return $this;
    }
}
