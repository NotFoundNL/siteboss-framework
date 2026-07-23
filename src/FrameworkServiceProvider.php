<?php

namespace NotFound\Framework;

use Illuminate\Console\Command;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schedule;
use NotFound\Framework\Auth\Notifications\VerifyEmail;
use NotFound\Framework\Http\Middleware\EnsureUserHasRole;
use NotFound\Framework\Http\Middleware\SetAndForgetLocale;
use NotFound\Framework\Jobs\RunTableActions;
use NotFound\Framework\Models\Lang;
use NotFound\Framework\Services\Assets\TableActionService;
use NotFound\Framework\Services\CmsExchange\ExchangeConsoleService;
use NotFound\Framework\Services\Indexer\IndexBuilderService;
use NotFound\Framework\View\Components\ConfigurationCheck;
use NotFound\Framework\View\Components\Forms\Form;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FrameworkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('siteboss')
            ->hasViews()
            ->hasTranslations()
            ->discoversMigrations()
            ->runsMigrations()
            ->hasRoute('api');
    }

    public function packageBooted(): void
    {
        $this->commands([
            Artisan::command('siteboss:index-site {--debug : Display debug messages} {--fresh : Empty local search table}', function ($debug, $fresh) {
                $indexer = new IndexBuilderService($debug, $fresh);
                $indexer->run();

                return Command::SUCCESS;
            })->purpose('Index site for local search'),

            Artisan::command('siteboss:cms-import {--debug : Display debug messages} {--dry : Dry Run}', function ($debug, $dry) {
                $exchanger = new ExchangeConsoleService($debug, $dry);
                $exchanger->import();

                return Command::SUCCESS;
            })->purpose('Import CMS changes to the database'),

            Artisan::command('siteboss:table-actions {--debug : Display debug messages} {--dry : Dry Run}', function () {
                $actions = new TableActionService($this->option('debug'), $this->option('dry'));
                $actions->run();

                return Command::SUCCESS;
            })->purpose('Run the retention actions configured for the CMS tables'),
        ]);

        // Registered after booting, so the schedule this is added to is the
        // same instance the console kernel builds.
        $this->app->booted(function () {
            Schedule::job(new RunTableActions)->daily();
        });
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'siteboss');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'siteboss');

        Blade::component('formbuilder-form', Form::class);
        Blade::component('configuration-check', ConfigurationCheck::class);
        Blade::componentNamespace('NotFound\\Framework\\View\\Components\\Forms\\Fields', 'fields');

        // Package-specific configs are merged so they are available without publishing.
        // Consumers should publish and customise these files via `php artisan vendor:publish --tag=siteboss-framework`.
        $this->mergeConfigFrom(__DIR__.'/../config/siteboss.php', 'siteboss');
        $this->mergeConfigFrom(__DIR__.'/../config/openid.php', 'openid');
        $this->mergeConfigFrom(__DIR__.'/../config/clamav.php', 'clamav');
        $this->mergeConfigFrom(__DIR__.'/../config/indexer.php', 'indexer');
        $this->mergeConfigFrom(__DIR__.'/../config/laravellocalization.php', 'laravellocalization');
        $this->mergeConfigFrom(__DIR__.'/../config/support.php', 'support');

        $this->publishes([
            // Package-specific configs (safe to publish and customise)
            __DIR__.'/../config/siteboss.php' => config_path('siteboss.php'),
            __DIR__.'/../config/openid.php' => config_path('openid.php'),
            __DIR__.'/../config/clamav.php' => config_path('clamav.php'),
            __DIR__.'/../config/indexer.php' => config_path('indexer.php'),
            __DIR__.'/../config/laravellocalization.php' => config_path('laravellocalization.php'),
            __DIR__.'/../config/support.php' => config_path('support.php'),
            // These configs must be manually merged into the host app's existing files —
            // auth.php and honeypot.php contain guards/providers that extend the defaults.
            // CSS asset
            __DIR__.'/../resources/css/siteboss.css' => public_path('assets/static/siteboss.css'),
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

    public function packageRegistered(): void
    {
        app('router')->aliasMiddleware('set-forget-locale', SetAndForgetLocale::class);
        app('router')->aliasMiddleware('role', EnsureUserHasRole::class);
    }
}
