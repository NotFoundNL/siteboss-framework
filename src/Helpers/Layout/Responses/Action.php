<?php

namespace NotFound\Framework\Helpers\Layout\Responses;

/**
 * Response
 *
 * This will create the data required to display feedback on the frontend.
 *
 * Use this class to send a response to the frontend. Send only a response and no other data.
 */
abstract class Action
{
    public function __construct(
        public string $type,
        public object $data = new \stdClass
    ) {}
}
