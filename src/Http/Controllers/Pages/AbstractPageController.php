<?php

namespace NotFound\Framework\Http\Controllers\Pages;

abstract class AbstractPageController extends PageController
{
    public function __invoke()
    {
        $viewName = $this->getViewName();

        return view($viewName);
    }

    abstract protected function getViewName(): string;
}
