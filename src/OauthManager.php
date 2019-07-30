<?php

namespace JKB\Oauth;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;

class OauthManager
{
    public $key;

    public $dirver;

    public $expireTime;

    public $createTokenMethodName;

    public $storeTokenMethodName;

    public $dropTokenMethodName;

    /**
     * OauthManager constructor.
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
        $this->key = config('jkb.jwt_key', 'Fantastic.Taylor.Otwell');
        $this->dirver = config('jkb.driver', 'cache');
        $this->cacheTag = config('jkb.cache_tag');
        $this->reToken = config('jkb.retoken');
        $this->expireTime = config('jkb.cache_expire_time');
        $this->oauthModel = config('jkb.oauth_model');
    }

    /**
     * init MethodName
     */
    private function initMethodName()
    {
        $this->createTokenMethodName = 'create' . ucfirst(strtolower($this->dirver)) . 'Token';
        $this->storeTokenMethodName = 'store' . ucfirst(strtolower($this->dirver)) . 'Token';
        $this->dropTokenMethodName = 'drop' . ucfirst(strtolower($this->dirver)) . 'Token';
    }

    /**
     * @param $role
     * @param $roleType
     * @param $roleClass
     * @return string
     */
    public function generateToken($role, $roleType, $roleClass)
    {
        $this->{$this->dropTokenMethodName}($role, $roleType);

        $token = $this->createOauthToken($role, $roleType, $roleClass);
        ding()->text($token);
        return $token;
    }

    /**
     * @param $role
     * @param $roleType
     */
    private function dropDatabaseToken($role, $roleType)
    {
        if (true === $this->reToken) {
            $this->oauthModel::where('role_id', $role->id)->where('role_type', $roleType)->delete();
        }
    }

    /**
     * @param $role
     * @param $roleType
     */
    private function dropCacheToken($role, $roleType)
    {
        if (true == $this->reToken) {
            Cache::tags([$this->cacheTag])->pull($roleType . '@' . $role->id);
        }
    }

    /**
     * @param $role
     * @param $roleType
     * @param $roleClass
     * @return string
     */
    private function createOauthToken($role, $roleType, $roleClass)
    {

        return tap($this->{$this->createTokenMethodName}($role, $roleType, $roleClass), function ($token) use ($role, $roleType, $roleClass) {
            $this->{$this->storeTokenMethodName}($token, $role, $roleType, $roleClass);
        });
    }

    /**
     * @param $role
     * @param $roleType
     * @return string
     */
    private function createDatabaseToken($role, $roleType, $roleClass)
    {
        return md5($roleType . $role->id . time());
    }

    /**
     * @param $role
     * @return string
     */
    private function createCacheToken($role, $roleType, $roleClass)
    {
        return JWT::encode([
            'role'       => $role,
            'cache_key'  => $roleType . '@' . $role->id,
            'role_type'  => $roleType,
            'role_class' => $roleClass,
            'time'       => time(),
        ], $this->key);
    }

    /**
     * @param $token
     * @param $role
     * @param $roleType
     * @param $roleClass
     */
    private function storeDatabaseToken($token, $role, $roleType, $roleClass)
    {
        $this->oauthModel::create([
            'token'      => $token,
            'role_type'  => $roleType,
            'role_id'    => $role->id,
            'role_class' => $roleClass,
            'expired_at' => Carbon::now()->addSeconds($this->expireTime)
        ]);
    }

    /**
     * @param $token
     * @param $role
     * @param $roleType
     * @param $roleClass
     */
    private function storeCacheToken($token, $role, $roleType, $roleClass)
    {
        Cache::tags([$this->cacheTag])->put($roleType . '@' . $role->id, $token, $this->expireTime);
    }
}