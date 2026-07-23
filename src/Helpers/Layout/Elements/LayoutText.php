<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutText extends AbstractLayout
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(string $text)
    {
        parent::__construct(type: 'Text');

        $this->properties->text = $text;
    }
}
