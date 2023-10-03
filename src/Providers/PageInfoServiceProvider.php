<?php

namespace NotFound\Framework\Providers;

use Illuminate\Support\ServiceProvider;
use NotFound\Framework\Models\PageInfo;

class PageInfoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('pageinfo', function () {
            return new PageInfo();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
