<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Framework\Models\Editor\AbstractEditor;
use NotFound\Framework\Services\Assets\TableService;


class DefaultEditor extends AbstractEditor
{
    private array $filters;
    protected TableService $ts;

    public function __construct($filters, $ts)
    {
        parent::__construct($filters, $ts);
    }
}