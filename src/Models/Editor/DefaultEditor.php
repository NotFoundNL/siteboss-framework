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
        $bar = new LayoutBar();

        $bar = $this->addNewButton($bar);

        $bar = $this->addPager($bar, $pager);

        $bar = $this->addSearchBox($bar);

        return $bar;
    }

    public function getBottomBar(): LayoutBar
    {
        $bar = new LayoutBar();

        $bar = $this->addNewButton($bar);

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

    protected function addPager(LayoutBar $bar, LayoutPager $pager)
    {
        return $bar->addPager($pager);
    }

    protected function addSearchBox(LayoutBar $bar)
    {
        return $bar->addSearchBox(new LayoutSearchBox(''));
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

    public function filters(): array
    {
        return $this->ts->getRequestParameters('filter') ?? [];
    }

    public function filterToParams(): string
    {
        if (empty($this->filters())) {
            return '';
        }
        $filterParams = '';
        foreach ($this->filters() as $key => $value) {
            $filterParams .= '&filter['.$key.']='.$value;
        }

        return $filterParams;
    }
}
