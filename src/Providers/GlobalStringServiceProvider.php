<?php

namespace NotFound\Framework\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use NotFound\Framework\Services\Assets\AssetValues;
use NotFound\Framework\Services\Assets\GlobalPageService;

class GlobalStringServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View()->composer('*', function ($view) {
            $view->with('g', Cache::remember('g', 3600, function () {
                $gp = new GlobalPageService;
                $globalPageValues = new AssetValues($gp->getCachedValues());

                return $globalPageValues;
            }));
        });
    }
}
