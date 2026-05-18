<?php

namespace NotFound\Framework\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /*
     * Can check for user roles in the router.
     * Should be used like this:
     *  Route::middleware('role:tasks');
     */

    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth('openid')->user()?->checkRights($role)) {
            abort(403, 'No permission for this resource');
        }

        return $next($request);
    }
}
