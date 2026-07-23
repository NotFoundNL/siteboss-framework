<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

use NotFound\Framework\Helpers\Layout\Enums\LayoutRequestMethod;
use NotFound\Framework\Helpers\Layout\Inputs\AbstractInput;

/**
 * LayoutForm
 */
class LayoutForm extends AbstractContainer
{
    public function __construct(string $url)
    {
        parent::__construct(type: 'Form');

        $this->properties->url = $url;
        $this->properties->method = LayoutRequestMethod::POST;
    }

    public function setMethod(LayoutRequestMethod $method): self
    {
        $this->properties->method = $method;

        return $this;
    }

    public function addButton(LayoutButton $btn): self
    {
        $this->items->add($btn->build());

        return $this;
    }

    public function addText(LayoutText $text): self
    {
        $this->items->add($text->build());

        return $this;
    }

    public function addInput(AbstractInput $input): self
    {
        $this->items->add($input->build());

        return $this;
    }

    public function addTitle(LayoutTitle $title): self
    {
        $title->setSize(4);
        $title->setUnderline();
        $this->items->add($title->build());

        return $this;
    }

    // TODO: think of better solution some time
    public function addComponent($component): self
    {
        $this->items->add($component->buildAutoLayoutClass());

        return $this;
    }
}
