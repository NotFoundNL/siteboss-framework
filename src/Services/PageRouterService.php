<?php

namespace NotFound\Framework\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use NotFound\Framework\Models\CmsRedirect;
use NotFound\Framework\Models\Menu;

class PageRouterService
{
    private bool $output = false;

    private $redirects;

    public function create()
    {
        try {
            $redirects = [];
            // Do not use cache when running in console, as cache may be faulty
            if (app()->runningInConsole()) {
                $routes = Menu::siteRoutes()->with(['template', 'children'])->get();
            } else {
                $routes = Cache::rememberForever('page_routes', function () {
                    return Menu::siteRoutes()->with(['template', 'children'])->get();
                });
            }

            Route::group(
                [
                    'prefix' => LaravelLocalization::setLocale(),
                    'middleware' => [LaravelLocalizationViewPath::class],
                ], function () use ($routes) {
                    $this->setRouteList($routes);
                }
            );

            CmsRedirect::whereNotIn('url', array_keys($this->redirects))->get()->map(function ($redirect) {
                $this->redirects[$redirect->url] = $redirect->redirect;
            });

            foreach ($this->redirects as $from => $to) {
                Route::redirect($from, $to);
            }

        } catch (Exception $e) {
            if (app()->runningInConsole()) {
                $this->cliError($e->getMessage());
            }
        }
        if ($this->output) {
            echo "\n\t---------------------------------------------------------------\n";
        }
    }

    private function setRouteList($routes)
    {
        foreach ($routes as $route) {
            if (isset($route->children) && isset($route->children[0])) {
                $this->setPageAsRoute($route->children[0], true);
            } else {
                dd('No child route set for site');
            }

            foreach ($route->children as $page) {
                $this->setRoutes($page);
            }
        }
    }

    private function setRoutes(Menu $page, $redirect = false)
    {
        if (! $redirect) {
            $redirect = $this->getRedirect($page->url);
            if ($redirect) {
                $this->redirects[$redirect] = $page->url;
            }
        }

        $this->setPageAsRoute($page, false, $redirect);

        // Do the same for the page children recursive.
        if ($page->has('children')) {
            Route::prefix($page->url)->group(function () use ($page, $redirect) {
                foreach ($page->children as $child) {
                    if ($redirect) {
                        $newRedirect = $redirect.'/'.$child->url;
                    }
                    $this->setRoutes($child, $newRedirect);
                }
            });
        }
    }

    private function getRedirect($route)
    {
        if ($redirect = CmsRedirect::where('redirect', $route)->first()) {
            return $redirect->url;
        }

        return false;
    }

    private function setPageAsRoute(Menu $page, $isRoot = false, $redirect = false)
    {
        $route = '/';
        if (! $isRoot) {
            $paramsUrl = $page->getParamsUrl();
            $route .= $page->url.$paramsUrl;
        }

        $className = $this->getControllerClassName($page);

        if ($className) {
            Route::get($route, [$className, '__invoke'])
                ->name('page.'.$page->id)
                ->defaults('page_id', $page->id);

            if ($redirect) {
                $array = Route::getGroupStack();
                $prefix = implode('/', array_column(array_slice($array, 2 - count($array), count($array) - 2), 'prefix'));
                $this->redirects[$redirect] = $prefix.$route;
            }
        }
    }

    private function getControllerClassName(Menu $page): string|false
    {
        if ($page->template === null) {
            return false;
        }

        $pageClassName = sprintf('App\\Http\\Controllers\\Page\\%sController', ucfirst($page->template->filename));

        if (class_exists($pageClassName)) {
            return $pageClassName;
        }

        if (app()->runningInConsole()) {
            $this->cliError($pageClassName.' does not exist.');
        } else {
            dd('Class does not exist: '.$pageClassName);
        }

        return false;
    }

    private function cliError($message): void
    {
        if (! $this->output) {
            printf("\n\t---------------------------------------------------------------");
            printf("\n\tSITEBOSS: Unable to set site routes.\n\n\tError:");
            $this->output = true;
        }
        printf("\n\t- %s", $message);
    }
}
