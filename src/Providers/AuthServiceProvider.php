<?php

//this file is published by the siteboss-framework package

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
    protected $policies = [
        'NotFound\Framework\Models\Forms\Data' => 'NotFound\Framework\Policies\Forms\DataPolicy',
        'NotFound\Framework\Models\Forms\Form' => 'NotFound\Framework\Policies\Forms\FormPolicy',
        'NotFound\Framework\Models\Forms\Category' => 'NotFound\Framework\Policies\Forms\CategoryPolicy',
        'NotFound\Framework\Models\Table' => 'NotFound\Framework\Policies\TablePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::extend('openid-token', function ($app, $name, array $config) {
            return new OpenIDGuard(Auth::createUserProvider($config['provider']), $app->request);
        });

        Auth::provider('openid-user-provider', function ($app, array $config) {
            return new OpenIDUserProvider($app->make($config['model']));
        });
    }
}
