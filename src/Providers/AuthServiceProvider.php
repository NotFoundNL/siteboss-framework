<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use NotFound\Framework\Http\Guards\OpenIDGuard;
use NotFound\Framework\Providers\Auth\OpenIDUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        // $this->registerPolicies();

        Auth::extend('openid-token', function ($app, $name, array $config) {
            return new OpenIDGuard(Auth::createUserProvider($config['provider']), $app->request);
        });

        Auth::provider('openid-user-provider', function ($app, array $config) {
            return new OpenIDUserProvider($app->make($config['model']));
        });
    }
}
