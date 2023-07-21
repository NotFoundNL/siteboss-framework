<?php

namespace NotFound\Framework\Auth\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;

class EnsureEmailIsVerified
{
    /**
     * Specify the redirect route for the middleware.
     *
     * @param  string  $route
     * @return string
     */
    public static function redirectTo($route)
    {
        return static::class.':'.$route;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {
            return [
                'result' => 'error',
                'message' => __('siteboss::auth.verify_email'),
                'buttonText' => __('siteboss::auth.verify_email_resend'),
                'link' => route('siteboss.verification.send', ['locale' => app()->getLocale()]),
            ];
        }

        return $next($request);
    }
}
