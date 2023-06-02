<?php

namespace NotFound\Framework\Providers;

use App\Http\Guards\OpenIDGuard;
use NotFound\Framework\Providers\Auth\OpenIDUserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'NotFound\Framework\Models\Forms\Data' => 'App\Policies\Forms\DataPolicy',
        'NotFound\Framework\Models\Forms\Form' => 'App\Policies\Forms\FormPolicy',
        'NotFound\Framework\Models\Forms\Category' => 'App\Policies\Forms\CategoryPolicy',
        'NotFound\Framework\Models\Table' => 'App\Policies\TablePolicy',
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
