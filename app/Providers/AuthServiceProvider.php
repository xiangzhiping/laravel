<?php

namespace App\Providers;

use App\Component\Auth\ApiGuard;
use App\Component\Auth\ApiProvider;
use App\Component\Auth\WebGuard;
use App\Component\Auth\WebProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $isApiRequest = true;
        if ($isApiRequest) {
            \Auth::extend('x-driver', function ($app, $name, array $config) {
                return new ApiGuard(new ApiProvider('api_token'), app('request'), 'api_token', 'api_token');
            });
        } else {
            \Auth::extend('x-driver', function ($app, $name, array $config) {
                return new WebGuard(new WebProvider(), 'user');
            });
        }
    }
}
