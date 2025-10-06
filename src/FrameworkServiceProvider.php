<?php

namespace NotFound\Framework;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use NotFound\Framework\Auth\Notifications\VerifyEmail;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\View\Components\ConfigurationCheck;
use NotFound\Framework\View\Components\Forms\Form;

class FrameworkServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'siteboss');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'siteboss');

        Blade::component('formbuilder-form', Form::class);
        Blade::component('configuration-check', ConfigurationCheck::class);
        Blade::componentNamespace('NotFound\\Framework\\View\\Components\\Forms\\Fields', 'fields');

        $this->publishes([
            __DIR__.'/../config/app.php' => config_path('app.php'),
            __DIR__.'/../config/auth.php' => config_path('auth.php'),
            __DIR__.'/../config/honeypot.php' => config_path('honeypot.php'),
            __DIR__.'/../config/siteboss.php' => config_path('siteboss.php'),
            __DIR__.'/../config/openid.php' => config_path('openid.php'),
            __DIR__.'/../config/clamav.php' => config_path('clamav.php'),
            __DIR__.'/../config/database.php' => config_path('database.php'),
            __DIR__.'/../config/forwards.php' => config_path('forwards.php'),
            __DIR__.'/../config/indexer.php' => config_path('indexer.php'),
            __DIR__.'/../config/laravellocalization.php' => config_path('laravellocalization.php'),
            __DIR__.'/../resources/css/siteboss.css' => public_path('assets/static/siteboss.css'),
            __DIR__.'/Providers/AuthServiceProvider.php' => app_path('Providers/AuthServiceProvider.php'),
        ], 'siteboss-framework');

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {

            // todo: get value from users current lang;
            App::setLocale(Lang::current()->url);

            $blockUrl = $url.'&block=1';

            return (new MailMessage)
                ->subject(__('siteboss::auth.verify_email_button').' '.config('app.name'))
                ->view('siteboss::emails.verify-email', ['url' => $url, 'blockUrl' => $blockUrl]);
        });
    }

    public function register(): void
    {
        app('router')->aliasMiddleware('set-forget-locale', \NotFound\Framework\Http\Middleware\SetAndForgetLocale::class);
        app('router')->aliasMiddleware('role', \NotFound\Framework\Http\Middleware\EnsureUserHasRole::class);
    }
}
