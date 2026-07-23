<?php

namespace NotFound\Framework\Helpers\Layout\Elements;

use NotFound\Framework\Helpers\Layout\LayoutResponse;

/**
 * LayoutPage
 *
 * The page is the root element for most API calls. Do not embed a LayoutPage in other UI elements (except for containers that have no UI)
 */
class LayoutPage extends AbstractContainer
{
    public function __construct(string $title)
    {
        parent::__construct(type: 'Page');

        $this->properties->title = $title;
    }

    public function addWidget(LayoutWidget $widget): self
    {
        $this->items->add($widget->build());

        return $this;
    }

    public function addBreadCrumb(LayoutBreadcrumb $breadcrumb): self
    {
        $this->items->add($breadcrumb->build());

        return $this;
    }

    public function addTitle(LayoutTitle $title): self
    {
        $this->items->add($title->build());

        return $this;
    }

    public function addBar(LayoutBar $bar): self
    {
        $this->items->add($bar->build());

        return $this;
    }

    public function addTabs(LayoutTabs $tabs): self
    {
        $this->items->add($tabs->build());

        return $this;
    }

    public function build(): object
    {
        return (object) [
            'type' => $this->type,
            'items' => $this->items,
            'properties' => $this->properties,
        ];
    }

    public static function unauthorized()
    {
        $page = new LayoutPage(__('response.error.unauthorized'));

        $widget = new LayoutWidget(__('response.error.unauthorizedDescription'));
        $widget->addText(new LayoutText(__('response.error.askForAdmin')));

        $page->addWidget($widget);

        $response = new LayoutResponse($page);

        return $response->build();
    }
}
