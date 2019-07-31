<?php

namespace Fengxing2017\Oauth\Auth;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider as Provider;
use Illuminate\Support\Facades\Cache;

class UserProvider implements Provider
{
    /**
     * @var string
     */
    public $token = 'token';

    /**
     * @var
     */
    public $driver;

    /**
     * @var
     */
    public $jwtKey;

    /**
     * @var
     */
    public $cacheTag;

    /**
     * @var
     */
    public $oauthModel;

    /**
     * UserProvider constructor.
     */
    public function __construct()
    {
        $this->initConfig();
        $this->initMethodName();
    }

    /**
     * 初始化配置属性
     */
    private function initConfig()
    {
        $this->driver = config('jkb.driver', 'database');
        $this->jwtKey = config('jkb.jwt_key', 'Fantastic.Taylor.Otwell');
        $this->cacheTag = config('jkb.cache_tag');
        $this->oauthModel = config('jkb.oauth_model');
    }

    /**
     * 初始化驱动方法名
     */
    private function initMethodName()
    {
        $this->getUserMethodName = 'get' . ucfirst(strtolower($this->driver)) . 'User';
    }

    /**
     * Retrieve a user by their unique identifier.
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return null;
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    /**
     * Update the "remember me" token for the given user in storage.
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return bool
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        return true;
    }

    /**
     * Retrieve a user by the given credentials.
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->{$this->getUserMethodName}($credentials[$this->token]);
    }

    /**
     * Rules a user against the given credentials.
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {

    }

    /**
     * 获取数据库认证用户
     * @param $accessToken
     * @return null
     */
    private function getDatabaseUser($accessToken)
    {
        try {
            if (!($jkbOauthToken = $this->oauthModel::whereToken($accessToken)->first())) {
                return null;
            }

            if (Carbon::now()->timestamp > Carbon::parse($jkbOauthToken->expired_at)->timestamp) {
                return null;
            }

            return $jkbOauthToken->role_class::find($jkbOauthToken->role_id);
        } catch (\Exception $exception) {
            return null;
        }

    }

    /**
     * 获取缓存认证用户
     * @param $accessToken
     * @return null
     */
    private function getCacheUser($accessToken)
    {
        try {
            $jwt = $this->getJWTOrigin($accessToken);

            if ($accessToken != $this->accessTokenByCachekey($jwt->cache_key)) {
                return null;
            }

            return new $jwt->role_class(collect($jwt->role)->toArray());
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * 提取jwt数据
     * @param $accessToken
     * @return object
     */
    private function getJWTOrigin($accessToken)
    {
        return JWT::decode($accessToken, $this->jwtKey, ['HS256']);
    }

    /**
     * 通过jwt获取缓存数据
     * @param $cacheKey
     * @return mixed
     */
    private function accessTokenByCachekey($cacheKey)
    {
        return Cache::tags([$this->cacheTag])->get($cacheKey);
    }
}