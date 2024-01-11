<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBreadcrumb;

abstract class AbstractEditor
{
    public function __construct(protected ?array $filters, protected TableService $ts)
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

    public function getBreadCrumbs(bool $edit = false): LayoutBreadCrumb
    {
        $table = $this->ts->getAssetModel();
        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem($table->name, ($edit) ? '/table/'.$table->url.'/?'.$this->filterToParams() : null);
        if($edit)
        {
            $breadcrumb->addItem('edit');
        }

        return $breadcrumb;
    }

    public function editBreadCrumbs(bool $edit = false): LayoutBreadCrumb
    {
        $breadcrumb = $this->overviewBreadCrumbs();
        $breadcrumb->addItem('Bewerk cursus');

        return $breadcrumb;
    }

    public function filters(): array
    {
        return $this->filters ?? [];
    }

    public function filterToParams(): string
    {
        if (! $this->filters) {
            return '';
        }
        $filterParams = '';
        foreach ($this->filters as $key => $value) {
            $filterParams .= '&filter['.$key.']='.$value;
        }

        return $filterParams;
    }
}
