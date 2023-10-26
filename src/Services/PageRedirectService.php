<?php

namespace NotFound\Framework\Services;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use NotFound\Framework\Models\CmsRedirect;

class PageRedirectService
{
    public static function getRoutes(): void
    {
        $redirects = CmsRedirect::where('enabled', true)->orderByRaw('CHAR_LENGTH(`url`) DESC')->get();
        foreach ($redirects as $redirect) {

            if ($redirect->recursive) {
                Route::redirect($redirect->url, $redirect->redirect, 301);
            } else {
                Route::any($redirect->url.'{any}', function ($pages) use ($redirect) {
                    $pages = trim($pages, '/');
                    if ($redirect->rewrite) {
                        $pages = '';
                    }

                    return Redirect::to($redirect->redirect.$pages);
                })->where('any', '.*');
            }
        }
    }
}
