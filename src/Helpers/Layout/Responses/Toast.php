<?php

namespace NotFound\Framework\Helpers\Layout\Responses;

/**
 * Toast
 *
 * This will create the data required to display feedback via a Toast on the frontend.
 */
class Toast extends Action
{
    public function __construct(string $message, string $status = 'ok')
    {
        parent::__construct('Toast');

        $this->data->message = $message;
        $this->data->status = $status;
    }
}
