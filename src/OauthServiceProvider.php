<?php

namespace Fengxing2017\Oauth;

use Fengxing2017\Oauth\Auth\UserGuard;
use Fengxing2017\Oauth\Auth\UserProvider;
use Fengxing2017\Oauth\Console\DeleteExpireToken;
use Fengxing2017\Oauth\Console\JkbCommons;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class OauthServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
        JkbCommons::class,
        DeleteExpireToken::class
    ];

    /**
     * middleware groups
     * @var
     */
    protected $middlewareGroups;

    /**
     * 注册相关服务
     */
    public function register()
    {
        $this->registerOauthManager();
        $this->setJkbAuthConfig();
        $this->initMiddlewareGroups();
        $this->registerMiddlewareGroups();
        $this->commands($this->commands);
    }

    /**
     * 注册服务提供者
     */
    protected function registerOauthManager()
    {
        $this->app->singleton(OauthManager::class, function ($app) {
            return new OauthManager();
        });
    }

    /**
     * 初始化认证配置
     * @return void
     */
    protected function setJkbAuthConfig()
    {
        foreach (config('jkb.guards') as $guardName => $guradConfig) {
            config(array_dot([
                'guards' => [
                    $guardName => [
                        'driver'   => 'jkb-guard',
                        'provider' => $guardName,
                    ]
                ],

                'providers' => [
                    $guardName => [
                        'driver' => 'jkb-provider',
                        'use'    => $guardName
                    ],
                ],
            ], 'auth.'));
        }
    }

    /**
     * 初始化中间件组
     */
    protected function initMiddlewareGroups()
    {
        foreach (config('jkb.guards') as $guardName => $guardConfig) {
            foreach ($guardConfig['before_auth'] as $middleware) {
                $this->middlewareGroups[$guardName][] = $middleware . ':' . $guardName;
            }
            $this->middlewareGroups[$guardName][] = 'auth:' . $guardName;
            foreach ($guardConfig['before_check'] as $middleware) {
                $this->middlewareGroups[$guardName][] = $middleware . ':' . $guardName;
            }
        }
    }

    /**
     * 注册路由中间件组
     */
    protected function registerMiddlewareGroups()
    {
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }

    /**
     * 注册认证提供者
     * 注册认证守卫
     * 发布文件
     */
    public function boot()
    {
        $this->registerProvider();
        $this->registerGuard();
        $this->vendorPublish();
    }

    /**
     * 注册认证提供者
     */
    private function registerProvider()
    {
        Auth::provider('jkb-provider', function ($app, $config) {
            return app()->make(UserProvider::class, [
                'guard' => $config['use']
            ]);
        });
    }

    /**
     * 注册认证守卫
     */
    private function registerGuard()
    {
        Auth::extend('jkb-guard', function ($app, $name, array $config) {
            return app()->make(UserGuard::class, [
                'provider' => Auth::createUserProvider($config['provider']),
                'request'  => $app->request,
                'name'     => $name
            ]);
        });
    }

    /**
     * 发布配置及数据库迁移文件
     */
    private function vendorPublish()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/config' => config_path()], 'jkb-oauth-config');
            $this->publishes([__DIR__ . '/database/migrations' => database_path('migrations')], 'jkb-oauth-migrations');
        }
    }

}