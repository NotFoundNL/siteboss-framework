<?php

namespace NotFound\Framework\Helpers\Layout\Responses;

/**
 * Modal
 *
 * This will create the data required to display a modal dialog on the frontend.
 */
class Modal extends Action
{
    public function __construct(string $message)
    {
        parent::__construct('Modal');

        $this->data->message = $message;
    }
}
