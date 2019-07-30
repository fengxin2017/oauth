<?php

namespace Fengxing2017\Oauth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Fengxing2017\Oauth\Auth\UserGuard;
use Fengxing2017\Oauth\Auth\UserProvider;
use Fengxing2017\Oauth\Middleware\OringinCheck;

class OauthServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * @var array
     */
    protected $routeMiddleware = [
        'jkb.origin.check' => OringinCheck::class
    ];

    /**
     * @var array
     */
    protected $middlewareGroups = [
        'jkb' => [
            'auth:jkb',
            'jkb.origin.check'
        ],
    ];


    /**
     * register
     */
    public function register()
    {
        $this->registerOauthManager();
        $this->registerOauthMiddleware();
        $this->loadJkbAuthConfig();
    }

    /**
     * register jkbOauthManager
     */
    protected function registerOauthManager()
    {
        $this->app->singleton(OauthManager::class, function ($app) {
            return new OauthManager();
        });
    }

    /**
     * Setup jkb auth configuration.
     * @return void
     */
    protected function loadJkbAuthConfig()
    {
        config(array_dot(config('jkb.auth', []), 'auth.'));
    }

    /**
     * registerOauthRoutes
     */
    protected function registerOauthMiddleware()
    {
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->aliasMiddleware($key, $middleware);
        }

        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }

    /**
     * register UserProvider
     * register JkbGuard
     */
    public function boot()
    {
        $this->registerProvider();
        $this->registerGuard();
        $this->vendorPublish();
    }

    /**
     * register UserProvider
     */
    private function registerProvider()
    {
        Auth::provider(config('jkb.auth.providers.jkb.driver'), function () {
            return app(UserProvider::class);
        });
    }

    /**
     * register JkbGuard
     */
    private function registerGuard()
    {
        Auth::extend(config('jkb.auth.guards.jkb.driver'), function ($app, $name, array $config) {
            return app()->make(UserGuard::class, [
                'provider' => Auth::createUserProvider($config['provider']),
                'request'  => $app->request,
            ]);
        });
    }

    /**
     * VendorPublish
     */
    private function vendorPublish()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config' => config_path()], 'jkb-oauth-config');
            $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'jkb-oauth-migrations');
        }
    }

}