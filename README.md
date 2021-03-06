<h1 align="center"> oauth </h1>

<p align="center"> A simple oauth for laravel-project.</p>

## 简介

> 基于三方登录（微信，微博，QQ，手机）mini-oauth 认证体系。

## 安装

```shell
$ composer require fengxin2017/oauth -vvv
```

## 发布配置
```
$ php artisan vendor:publish
```

## 创建数据库token存储表
```
$ php artisan migrate
```

## 创建配置中oauth_model对应的模型
```
$ php artisan make:model JkbOauthToken
```

## 如何使用
> 比如配置中使用的auth_middleware_groups是foo。

> 首先认证模型需要添加Authenticatable，当然你使用laravel自带的Illuminate\Auth\Authenticatable也是没问题的。

```
<?php

namespace App;
use Fengxin2017\Oauth\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User implements AuthenticatableContract
{
    use Authenticatable;
}

```
> 根据第三方返回信息(比如微信的open_id之类的检索出需要认证的模型)。

```
$user = Wechat::where('open_id',$openId)->first()->user;
```
> 调用Oauth门面类的generateTokenFor方法为认证模型颁发Token。

```
// Fengxin2017\Oauth\Facade\Oauth

Oauth::generateTokenFor($user, 'foo'); 
// Oauth::setGuard('foo')->generateTokenFor($user);
```

> 客户端支持get、post、header携带token发起请求。

```
// 客户端
axios.get('https://api.jkb.cn/getUser',{
    headers: {'Authorization': this.token}//设置header信息
}).then( res => {
    //
}).catch( error => {
    //
})

// 服务端
Route::middleware('foo')->get('/getUser',function(){
    dd(request()->user());
});

```

## 认证中间件组

> 可以配置多个，互不干扰。

## 关于token存储

> 支持database和cache，使用缓存请确保框架已开启相应服务。

## 认证前中间件

> 在检索认证模型前会触发配置中before_auth中间件组。已内置域名验证中间件，如不想使用可以移除。

## 验证前中间件

> 在检索认证模型后，验证认证模型是否合法之前会触发配置文件before_check中间件组。

## 生成Token会触发一个事件。

```
Fengxin2017\Oauth\Events\AccessTokenCreated::class
```

## 删除数据库过期Token
```
php artisan jkb:clear --tag=de
```

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/fengxin2017/oauth/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/fengxin2017/oauth/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-4 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT