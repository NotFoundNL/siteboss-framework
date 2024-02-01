<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBar;
use NotFound\Layout\Elements\LayoutBarButton;
use NotFound\Layout\Elements\LayoutBreadcrumb;

abstract class AbstractEditor
{
    public function __construct(protected TableService $ts)
    {

    }

    /**
     * preOverview
     *
     * Runs before the overview is rendered
     */
    public function preOverview(): void
    {

    }

    public function postOverview(): void
    {

    }

    public function preEdit(): void
    {

    }

    public function postEdit(): void
    {

    }

    public function preCreate(): void
    {

    }

    public function postCreate(): void
    {

    }

    public function getBar(): LayoutBar
    {
        $bar = new LayoutBar();

        $table = $this->ts->getAssetModel();

        if ($table->allow_create) {
            $addNew = $this->getNewButton();
            $bar->addBarButton($addNew);
        }

        return $bar;
    }

    public function getBottomBar(): LayoutBar
    {
        $bottomBar = $this->getBar();
        $bottomBar->noBackground();

        return $bottomBar;
    }

    public function getNewButton(): LayoutBarButton
    {
        $addNew = new LayoutBarButton('Nieuw');
        $table = $this->ts->getAssetModel();
        $addNew->setIcon('plus');
        $url = '/table/'.$table->url.'/0';
        if ($params = $this->filterToParams()) {
            $url .= '?'.ltrim($params, '&');
        }
        $addNew->setLink($url);

        return $addNew;
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
