<?php

namespace NotFound\Framework\Helpers\Layout\Responses;

/**
 * Reload
 *
 * This will reload the current page.
 */
class Reload extends Action
{
    public function __construct()
    {
        parent::__construct('Reload');
    }
}
