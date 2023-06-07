<?php

namespace NotFound\Framework\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use NotFound\Framework\Models\Menu;

class PageRouterService
{
    public function create()
    {
        try {
            // Do not use cache when running in console, as cache may be faulty
            // TODO: see if we can skip routing entirely when running cli
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
                    'middleware' => ['localeViewPath'],
                ], function () use ($routes) {
                    $this->setRouteList($routes);
                }
            );
        } catch (Exception $e) {
            if (app()->runningInConsole()) {
                echo '
                ---------------------------------------------------------------
                    SITEBOSS: Unable to set site routes.

                    Error:
                    '.$e->getMessage().'
                ---------------------------------------------------------------
            ';
            }
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

    private function setRoutes(Menu $page)
    {
        $this->setPageAsRoute($page);

        // Do the same for the page children recursive.
        if ($page->has('children')) {
            Route::prefix($page->url)->group(function () use ($page) {
                foreach ($page->children as $child) {
                    $this->setRoutes($child);
                }
            });
        }
    }

    private function setPageAsRoute(Menu $page, $isRoot = false)
    {
        $route = '/';
        if (! $isRoot) {
            $paramsUrl = $page->getParamsUrl();
            $route .= $page->url.$paramsUrl;
        }

        $className = $this->getControllerClassName($page);

        Route::get($route, [$className, '__invoke'])
            ->name('page.'.$page->id)
            ->defaults('page_id', $page->id);
    }

    private function getControllerClassName(Menu $page): string
    {

        if ($page->template !== null) {
            $pageClassName = sprintf('Siteboss\\App\\Http\\Controllers\\Page\\%sController', ucfirst($page->template->filename));

            if (! class_exists($pageClassName)) {
                dd('Class does not exist: '.$pageClassName);
            }

            return $pageClassName;
        }

        return '';
    }
}
