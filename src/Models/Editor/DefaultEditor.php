<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutPager;
use NotFound\Layout\Elements\LayoutSearchBox;

class DefaultEditor extends AbstractEditor
{
    public function __construct(protected TableService $ts)
    {
    }

    public function getTopBar(LayoutPager $pager): LayoutBar
    {
        $bar = $this->addNewButton(new LayoutBar);

        $bar->addPager($pager);
        $bar->addSearchBox(new LayoutSearchBox(''));

        return $bar;
    }

    public function getBottomBar(): LayoutBar
    {
        $bar = $this->addNewButton(new LayoutBar);

        $bar->noBackground();

        return $bar;
    }

    protected function addNewButton(LayoutBar $bar): LayoutBar
    {
        $table = $this->ts->getAssetModel();

        if (! $table->allow_create) {
            return $bar;
        }

        $addNew = new LayoutBarButton('Nieuw');
        $table = $this->ts->getAssetModel();
        $addNew->setIcon('plus');
        $url = '/table/'.$table->url.'/0';
        if ($params = $this->filterToParams()) {
            $url .= '?'.ltrim($params, '&');
        }
        $addNew->setLink($url);

        return $bar->addBarButton($addNew);
    }

    public function getBreadCrumbs(): LayoutBreadCrumb
    {
        $table = $this->ts->getAssetModel();
        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem($table->name);

        return $breadcrumb;
    }

    public function getBreadCrumbsEdit(): LayoutBreadCrumb
    {
        $table = $this->ts->getAssetModel();
        $breadcrumb = $this->getBreadCrumbs();
        end($breadcrumb->properties->items)->link = '/table/'.$table->url.'/?'.$this->filterToParams();
        $breadcrumb->addItem('edit');

        return $breadcrumb;
    }
}
