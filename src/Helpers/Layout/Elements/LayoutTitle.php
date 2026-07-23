<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutTitle extends AbstractLayout
{
    public function __construct($text)
    {
        parent::__construct(type: 'Title');

        $this->properties->text = $text;
    }

    public function setSize(int $size): self
    {
        $this->properties->size = $size;

        return $this;
    }

    public function setUnderline(): self
    {
        $this->properties->underline = true;

        return $this;
    }
}
