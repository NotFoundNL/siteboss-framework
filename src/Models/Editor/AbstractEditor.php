<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutPager;

abstract class AbstractEditor
{
    public function __construct(protected TableService $ts)
    {
    }

    abstract public function getTopBar(LayoutPager $pager): LayoutBar;

    abstract public function getBottomBar(LayoutPager $pager): LayoutBar;

    abstract public function getBreadCrumbs(): LayoutBreadCrumb;

    abstract public function getBreadCrumbsEdit(): LayoutBreadCrumb;

    abstract public function getOverviewUrl(): string;

    abstract public function filterParameters(): string;
}
