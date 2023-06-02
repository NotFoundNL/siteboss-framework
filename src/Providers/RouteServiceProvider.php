<?php

namespace NotFound\Framework\Providers;

use NotFound\Framework\Services\PageRouterService;
use App\View\Components\Forms\Form;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    const HOME = '/';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var null|string
     */
    protected $namespace = 'NotFound\\Framework\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
    public function boot()
    {
        resolve(\Illuminate\Routing\UrlGenerator::class)->forceScheme('https');

        parent::boot();

        Blade::component('formbuilder-form', Form::class);

        $this->configureRateLimiting();

        $this->routes(function () {
            if (file_exists(siteboss_path('routes/api.php'))) {
                Route::prefix('/')
                    ->group(siteboss_path('routes/api.php'));
            }

            Route::middleware('web')
                ->group(function () {
                    $pageRouter = new PageRouterService();
                    $pageRouter->create();
                });
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
