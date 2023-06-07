<?php

namespace NotFound\Framework;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use NotFound\Framework\View\Components\Forms\Form;

class FrameworkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'siteboss');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'siteboss');

        Blade::component('formbuilder-form', Form::class);
        Blade::componentNamespace('NotFound\\Framework\\View\\Components\\Forms\\Fields', 'fields');

        $this->publishes([
            __DIR__.'/../config/auth.php' => config_path('auth.php'),
            __DIR__.'/../config/siteboss.php' => config_path('siteboss.php'),
            __DIR__.'/../config/openid.php' => config_path('openid.php'),
            __DIR__.'/../config/clamav.php' => config_path('clamav.php'),
            __DIR__.'/../config/database.php' => config_path('database.php'),
            __DIR__.'/Providers/AuthServiceProvider.php' => app_path('Providers/AuthServiceProvider.php'),
            __DIR__.'/../database/seeders/DatabaseSeeder.php' => database_path('seeders/DatabaseSeeder.php'),
        ], 'siteboss-framework');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/app.php', 'app');

        app('router')->aliasMiddleware('set-forget-locale', \NotFound\Framework\Http\Middleware\SetAndForgetLocale::class);
        app('router')->aliasMiddleware('role', \NotFound\Framework\Http\Middleware\EnsureUserHasRole::class);
    }
}
