<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;
use NotFound\Layout\Elements\LayoutPager;
use NotFound\Layout\Elements\LayoutSearchBox;

abstract class AbstractEditor
{
    public function __construct(protected TableService $ts)
    {

    }

    abstract public function getTopBar(LayoutPager $pager): LayoutBar;

    abstract public function getBottomBar(): LayoutBar;

   abstract public function getBreadCrumbs(): LayoutBreadCrumb;

   abstract public function getBreadCrumbsEdit(): LayoutBreadCrumb;

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

    protected function addPager(LayoutBar $bar, LayoutPager $pager): LayoutBar
    {
        return $bar->addPager($pager);
    }

    protected function addSearchBox(LayoutBar $bar): LayoutBar
    {
        return $bar->addSearchBox(new LayoutSearchBox(''));
    }
}
