<?php

namespace NotFound\Framework\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasRole
{
    /*
     * Can check for user roles in the router.
     * Should be used like this:
     *  Route::middleware('role:tasks');
     */

    public function handle(Request $request, Closure $next, string $role)
    {
        if (! auth('openid')->user()->checkRights($role)) {
            return abort(403, 'No permission for this resource');
        }

        return $next($request);
    }
}
