<?php

namespace Fengxing2017\Oauth;

use Fengxing2017\Oauth\Auth\UserGuard;
use Fengxing2017\Oauth\Auth\UserProvider;
use Fengxing2017\Oauth\Middleware\OringinCheck;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

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
        'origin.check' => OringinCheck::class
    ];

    /**
     * @var array
     */
    protected $middlewareGroups;

    /**
     * register
     */
    public function register()
    {
        $this->registerOauthManager();
        $this->setJkbAuthConfig();
        $this->initMiddlewareGroups();
        $this->addCustomizeMiddlewares();
        $this->registerOauthMiddleware();
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
     * 初始化认证
     * @return void
     */
    protected function setJkbAuthConfig()
    {
        config(array_dot([
            'guards' => [
                'jkb' => [
                    'driver'   => 'jkb-guard',
                    'provider' => 'jkb'
                ]
            ],

            'providers' => [
                'jkb' => [
                    'driver' => 'jkb-provider',
                ],
            ],
        ], 'auth.'));
    }

    /**
     * 初始化中间件组
     */
    protected function initMiddlewareGroups()
    {
        $this->middlewareGroups = [
            config('jkb.middleware_group_name', 'jkb') => [
                'auth:jkb',
                'jkb.origin.check',
            ],
        ];
    }

    /**
     * 注册用户自定义中间件
     */
    protected function addCustomizeMiddlewares()
    {
        if ($middlewares = config('jkb.customize_middlewares', [])) {
            foreach ($middlewares as $middleware) {
                $this->middlewareGroups[config('jkb.middleware_group_name', 'jkb')][] = $middleware;
            }
        }
    }

    /**
     * 注册中间件
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
     * 加载获取token路由
     * 注册认证提供者
     * 扩展认证守卫
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
        Auth::provider('jkb-provider', function () {
            return app(UserProvider::class);
        });
    }

    /**
     * 扩展守卫
     */
    private function registerGuard()
    {
        Auth::extend('jkb-guard', function ($app, $name, array $config) {
            return app()->make(UserGuard::class, [
                'provider' => Auth::createUserProvider($config['provider']),
                'request'  => $app->request,
            ]);
        });
    }

    /**
     * 发布配置及数据库迁移文件
     */
    private function vendorPublish()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config' => config_path()], 'jkb-oauth-config');
            $this->publishes([__DIR__ . '/../database/migrations' => database_path('migrations')], 'jkb-oauth-migrations');
        }
    }

}