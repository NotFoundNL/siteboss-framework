<?php

namespace NotFound\Framework\Helpers\Layout\Responses;

/**
 * Modal
 *
 * This will create the data required to display a modal dialog on the frontend.
 */
class Download extends Action
{
    public function __construct(string $link)
    {
        parent::__construct('Download');

        $this->data->link = $link;
    }
}
