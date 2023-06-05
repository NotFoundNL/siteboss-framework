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
            __DIR__.'/../config/auth.php', 'auth'
        );

        

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'siteboss');
        $this->publishes([
            __DIR__.'/../config/siteboss.php' => config_path('siteboss.php'),
            __DIR__.'/../config/openid.php' => config_path('openid.php'),
        ], 'laravel-assets');
    }

    public function register(): void
    {
        app('router')->aliasMiddleware('set-forget-locale', \NotFound\Framework\Http\Middleware\SetAndForgetLocale::class);
        app('router')->aliasMiddleware('role', \NotFound\Framework\Http\Middleware\EnsureUserHasRole::class);
    }
}