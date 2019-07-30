<?php

namespace JKB\Oauth\Auth;

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
    public $key;

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
     * init Config
     */
    private function initConfig()
    {
        $this->driver = config('jkb.driver', 'database');
        $this->key = config('jkb.jwt_key', 'Fantastic.Taylor.Otwell');
        $this->cacheTag = config('jkb.cache_tag');
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
     * @param $accessToken
     * @return null
     */
    private function getCacheUser($accessToken)
    {
        try {
            $jwt = JWT::decode($accessToken, $this->key, ['HS256']);

            if ($accessToken != $this->accessTokenByCachekey($jwt->cache_key)) {
                return null;
            }

            return app($jwt->role_class, collect($jwt->role)->toArray());
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