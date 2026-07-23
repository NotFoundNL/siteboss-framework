<?php

namespace NotFound\Framework\Helpers\Layout;

use Illuminate\Support\Collection;
use NotFound\Framework\Helpers\Layout\Elements\AbstractLayout;
use NotFound\Framework\Helpers\Layout\Responses\Action;

class LayoutResponse
{
    private Collection $actions;

    private Collection $elements;

    private ?string $nextRequest = null;

    public function __construct(?AbstractLayout $ui = null, ?Action $action = null)
    {
        $this->actions = new Collection;
        $this->elements = new Collection;

        if ($action) {
            $this->actions->add($action);
        }

        if ($ui) {
            $this->elements->add($ui->build());
        }
    }

    public function addUIElement(AbstractLayout $element)
    {
        $this->elements->add($element->build());
    }

    public function addAction(Action $action)
    {
        $this->actions->add($action);
    }

    public function setNextRequest(string $url)
    {
        $this->nextRequest = $url;
    }

    public function build()
    {
        if (count($this->actions) + count($this->elements) == 0) {
            return (object) ['status' => 'error'];
        }

        $response = new \stdClass;
        $response->status = 'ok';

        if ($this->elements->isNotEmpty()) {
            $response->ui = $this->elements;
        }

        if ($this->actions->isNotEmpty()) {
            $response->actions = $this->actions;
        }

        if ($this->nextRequest !== null) {
            $response->next = $this->nextRequest;
        }

        return $response;
    }
}
