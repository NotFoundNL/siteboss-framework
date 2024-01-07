<?php

namespace NotFound\Framework\Models\Editor;

use NotFound\Framework\Services\Assets\TableService;
use NotFound\Layout\Elements\LayoutBreadcrumb;


abstract class AbstractEditor
{
    private ?array $filters;
    protected TableService $ts;

    public function __construct($filters, $ts)
    {
        $this->filters = $filters;
        $this->ts = $ts;
    }

    public function getBreadCrumbs(bool $edit = false): LayoutBreadCrumb
    {
        $table = $this->ts->getAssetModel();
        $breadcrumb = new LayoutBreadcrumb();
        $breadcrumb->addHome();
        $breadcrumb->addItem($table->name, '/table/'.$table->url.'/?'.$this->filterToParams());
        if($edit)
        {
            $breadcrumb->addItem('edit');
        }
        return $breadcrumb;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function filterToParams(): string
    {
        if(!$this->filters) return '';
        $filterParams='';
        foreach ($this->filters as $key=>$value)
        {
            $filterParams.='&filter['.$key.']='.$value;
        }
        return $filterParams;
    }
}