<?php

namespace NotFound\Framework\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SetAndForgetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocaleFromUrl();

        app()->setLocale($locale);
        $request->route()->forgetParameter('locale');

        return $next($request);
    }

    /**
     * Get the locale from the current url.
     * Check whether the api_prefix is used or not
     *
     * @return string the locale sent by the frontend
     */
    public function getLocaleFromUrl(): string
    {
        // segments starts at 1
        $localeSegment = 1;
        $currentUri = request()->route()->uri();
        if (substr($currentUri, 0, 1) !== '/') {
            $currentUri = '/'.$currentUri;
        }

        if (Str::startsWith($currentUri, config('app.api_prefix'))) {
            $localeSegment = $this->getApiPrefixSegmentsCount() + 1;
        }

        return request()->segment($localeSegment);
    }

    /**
     * Get the number of segments the api prefix has, so we can check
     * where the locale segment resides in the url.
     *
     * @return int number of segments the api_prefix has
     */
    private function getApiPrefixSegmentsCount(): int
    {
        $prefix = rawurldecode(config('app.api_prefix'));

        $segments = explode('/', $prefix);

        $prefixArray = array_values(array_filter($segments, function ($value) {
            return $value !== '';
        }));

        return count($prefixArray);
    }
}
