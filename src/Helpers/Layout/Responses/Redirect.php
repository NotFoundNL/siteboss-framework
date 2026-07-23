<?php

namespace NotFound\Framework\Helpers\Layout\Responses;

/**
 * Redirect
 *
 * This will push a new URL to the nuxt router
 */
class Redirect extends Action
{
    /**
     * __construct
     *
     * @param  mixed  $target  The path to navigate to (without locale!)
     * @return void
     */
    public function __construct(string $target)
    {
        parent::__construct('Redirect');
        $this->data->target = $target;
    }
}
