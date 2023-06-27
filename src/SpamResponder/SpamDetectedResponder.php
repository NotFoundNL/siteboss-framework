<?php

namespace NotFound\Framework\SpamResponder;

use Closure;
use Illuminate\Http\Request;
use Spatie\Honeypot\SpamResponder\SpamResponder;

class SpamDetectedResponder implements SpamResponder
{
    public function respond(Request $request, Closure $next)
    {
        return response('Deze request is gemarkeerd as spam.');
    }
}
