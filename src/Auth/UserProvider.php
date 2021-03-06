<?php

namespace Fengxin2017\Oauth\Auth;

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
     * For Respect
     * @var string
     */
    public $jwtKey = 'Fantastic-Taylor-Otwell';

    /**
     * @var null
     */
    public $guard;

    /**
     * @var
     */
    public $driver;

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
    public function __construct($guard = null)
    {
        $this->guard = $guard;
        $this->initConfig();
        $this->initMethodName();
    }

    /**
     * init Config
     */
    private function initConfig()
    {
        $this->driver = config('jkb.auth_middleware_groups.' . $this->guard . '.driver', 'database');
        $this->cacheTag = config('jkb.auth_middleware_groups.' . $this->guard . '.cache_tag', $this->guard);
        $this->oauthModel = config('jkb.oauth_model');
    }

    /**
     * init MethodName
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
     * @param $accessToken
     * @return null
     */
    private function getDatabaseUser($accessToken)
    {
        try {
            if (
            !($jkbOauthToken = $this->oauthModel::whereToken($accessToken)
                ->whereGuard($this->guard)
                ->where('expired_at', '>', Carbon::now())
                ->first())
            ) {
                return null;
            }

            return $jkbOauthToken->role_class::find($jkbOauthToken->role_id);
        } catch (\Exception $exception) {
            return null;
        }

    }

    /**
     * @param $accessToken
     * @return null
     */
    private function getCacheUser($accessToken)
    {
        try {
            $jwt = JWT::decode($accessToken, $this->jwtKey, ['HS256']);

            if (
                $accessToken != $this->accessTokenByCachekey($jwt->cache_key)
                || $jwt->guard != $this->guard
            ) {
                return null;
            }

            return new $jwt->role_class(collect($jwt->role)->toArray());
        } catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param $cacheKey
     * @return mixed
     */
    private function accessTokenByCachekey($cacheKey)
    {
        return Cache::tags([$this->cacheTag])->get($cacheKey);
    }
}