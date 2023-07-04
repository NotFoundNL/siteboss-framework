<?php

namespace NotFound\Framework\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use NotFound\Framework\Services\PageRouterService;

class RouteServiceProvider extends ServiceProvider
{
    const HOME = '/home';

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

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('web')
                ->group(function () {
                    $pageRouter = new PageRouterService();
                    $pageRouter->create();
                });

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Optionally load API routes
            if (file_exists(base_path('routes/api.php'))) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));
            }
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
