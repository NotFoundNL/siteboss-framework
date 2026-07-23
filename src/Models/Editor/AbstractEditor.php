<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Helpers\Layout\Elements\LayoutBar;
use NotFound\Framework\Helpers\Layout\Elements\LayoutBreadcrumb;
use NotFound\Framework\Helpers\Layout\Elements\LayoutPager;
use NotFound\Framework\Services\Assets\TableService;

abstract class AbstractEditor
{
    public function __construct(protected TableService $ts) {}

    abstract public function getTopBar(LayoutPager $pager): LayoutBar;

    abstract public function getBottomBar(LayoutPager $pager): LayoutBar;

    abstract public function getBreadCrumbs(bool $archiveView = false): LayoutBreadcrumb;

    abstract public function getBreadCrumbsEdit(): LayoutBreadcrumb;

    abstract public function getOverviewUrl(): string;

    abstract public function filterParameters(): string;
}
