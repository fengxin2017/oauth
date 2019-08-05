<h1 align="center"> oauth </h1>

<p align="center"> A simple oauth for laravel-project.</p>


## 安装

```shell
$ composer require fengxing2017/oauth -vvv
```

## 如何使用

> 比如使用的guard是'jkb'.

> 根据第三方返回信息(比如微信的open_id之类的检索出认证模型)

```
$user = Wechat::where('open_id',$openId)->user;
```
> 调用Oauth门面类的generateTokenFor方法生成Token

```
// Fengxing2017\Oauth\Facade\Oauth
Oauth::generateTokenFor($user, 'jkb'); // Oauth::setGuard('jkb')->generateTokenFor($user);
```

> 携带token发起请求

```
// 请求头需携带  "Authorization":"颁发的token值"  "Authorization" 可根据配置中 authorization_key 做任意修改      

Route::middleware('jkb')->get('/user',function(){
    dd(request()->user());
});

```

## 赦免令牌

> 开启赦免令牌可绕过系统认证，针对某种特殊人群你可以自定义认证
> 配置文件中修改 except => true
> 请求头携带k => v,如果k和配置中except_header_key的值相同并且v是在配置except_header_lists数组中，则可以绕过系统认证 


## 关于守卫

> Guards可以配置多个，互不干扰.

## 关于token存储

> 支持database和cache.

## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/fengxin2017/oauth/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/fengxin2017/oauth/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT