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

    public function getBottomBar(LayoutPager $pager): LayoutBar
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
        if ($params = $this->filterParameters()) {
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
        end($breadcrumb->properties->items)->link = '/table/'.$table->url.'/?'.$this->filterParameters();
        $breadcrumb->addItem('edit');

        return $breadcrumb;
    }

    public function getOverviewUrl(): string
    {
        $table = $this->ts->getAssetModel();
        $request = $this->ts->getRequestParameters() ?? [];
        $params = sprintf('page=%d&sort=%s&asc=%s', $request['page'] ?? 1, $request['sort'] ?? '', $request['asc'] ?? '');

        $url = sprintf('/table/%s/?%s&%s', $table->url, $params, $this->filterParameters());

        return $url;
}

    public function filterParameters(): string
    {
        $filters = $this->ts->getRequestParameters('filter') ?? [];
        if (empty($filters)) {
            return '';
        }
        $filterParams = [];
        foreach ($filters as $key => $value) {
            $filterParams[] = 'filter['.$key.']='.urlencode($value);
        }

        return implode('&', $filterParams);
    }
}
