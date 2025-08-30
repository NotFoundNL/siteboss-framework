<?php

namespace NotFound\Framework\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath;
use NotFound\Framework\Models\Menu;

class PageRouterService
{
    private bool $output = false;

    public function create()
    {
        try {
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
                    PageRedirectService::getRoutes();
                    $this->setRouteList($routes);
                }
            );
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
                Log::error('No child route set for site');
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

        if ($className) {
            Route::get($route, [$className, '__invoke'])
                ->name('page.'.$page->id)
                ->defaults('page_id', $page->id);
        }
    }

    private function getControllerClassName(Menu $page): string|false
    {
        if ($page->template === null) {
            return false;
        }

        $pageClassName = sprintf('App\\Http\\Controllers\\Page\\%sController', ucfirst($page->template->controller ?? $page->template->filename));

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
