<?php

namespace NotFound\Framework;


use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FrameworkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'siteboss');
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/app.php', 'app'
        );


        $this->loadTranslationsFrom(__DIR__.'/../lang', 'siteboss');
        $this->publishes([

        ], 'laravel-assets');
    }
}