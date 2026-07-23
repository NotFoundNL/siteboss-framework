<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

class LayoutSiteBoss extends AbstractLayout
{
    /**
     * __construct
     *
     * @return void
     */
    public function __construct(string $feature)
    {
        parent::__construct(type: 'SiteBoss');
        $this->properties->feature = $feature;
        $this->properties->text = '';
    }
}
