<?php

namespace NotFound\Framework\Http\Controllers\Pages;

abstract class AbstractPageController extends PageController
{
    abstract protected function getViewName(): string;

    public function __invoke()
    {
        $viewName = $this->getViewName();

        return view($viewName);
    }
}
