<?php

namespace Fengxing2017\Oauth;

use Carbon\Carbon;
use Fengxing2017\Oauth\Events\AccessTokenCreated;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class OauthManager
{
    /**
     * @var
     */
    public $jwtKey;

    /**
     * @var
     */
    public $dirver;

    /**
     * @var
     */
    public $expireTime;

    /**
     * @var
     */
    public $createTokenMethodName;

    /**
     * @var
     */
    public $storeTokenMethodName;

    /**
     * @var
     */
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
     * 初始化配置属性
     */
    private function initConfig()
    {
        $this->jwtKey = config('jkb.jwt_key', 'Fantastic.Taylor.Otwell');
        $this->dirver = config('jkb.driver', 'cache');
        $this->cacheTag = config('jkb.cache_tag');
        $this->reToken = config('jkb.retoken');
        $this->expireTime = config('jkb.cache_expire_time');
        $this->oauthModel = config('jkb.oauth_model');
    }

    /**
     * 初始化驱动方法名
     */
    private function initMethodName()
    {
        $this->createTokenMethodName = 'create' . ucfirst(strtolower($this->dirver)) . 'Token';
        $this->storeTokenMethodName = 'store' . ucfirst(strtolower($this->dirver)) . 'Token';
        $this->dropTokenMethodName = 'drop' . ucfirst(strtolower($this->dirver)) . 'Token';
    }

    /**
     * 生成Token
     * @param $role
     * @param $roleClass
     * @return string
     */
    public function generateToken($role, $roleClass)
    {
        $this->{$this->dropTokenMethodName}($role, $roleClass);

        return $this->createOauthToken($role, $roleClass);
    }

    /**
     * 根据配置决定是否删除数据库Token
     * @param $role
     * @param $roleClass
     */
    private function dropDatabaseToken($role, $roleClass)
    {
        if (true === $this->reToken) {
            $this->oauthModel::where('role_id', $role->id)->where('role_class', $roleClass)->delete();
        }
    }

    /**
     * 根据配置决定是否删除缓存Token
     * @param $role
     * @param $roleClass
     */
    private function dropCacheToken($role, $roleClass)
    {
        if (true == $this->reToken) {
            Cache::tags([$this->cacheTag])->pull($roleClass . '@' . $role->id);
        }
    }

    /**
     * 创建新Token
     * @param $role
     * @param $roleClass
     * @return string
     */
    private function createOauthToken($role, $roleClass)
    {
        return tap($this->{$this->createTokenMethodName}($role, $roleClass), function ($token) use ($role, $roleClass) {
            $this->{$this->storeTokenMethodName}($token, $role, $roleClass);
        });
    }

    /**
     * 创建新数据库Token | 派发时间
     * @param $role
     * @param $roleClass
     * @return string
     */
    private function createDatabaseToken($role, $roleClass)
    {
        return tap(md5($roleClass . $role->id . time()), function ($token) use ($role) {
            Event::dispatch(new AccessTokenCreated($token, $role->id, 'database'));
        });
    }

    /**
     * 创建新缓存Token | 派发时间
     * @param $role
     * @param $roleClass
     * @return string
     */
    private function createCacheToken($role, $roleClass)
    {
        return tap(JWT::encode([
            'role'       => $role,
            'cache_key'  => $roleClass . '@' . $role->id,
            'role_class' => $roleClass,
            'time'       => time(),
        ], $this->jwtKey), function ($token) use ($role) {
            Event::dispatch(new AccessTokenCreated($token, $role->id, 'cache'));
        });
    }

    /**
     * 保存数据库token
     * @param $token
     * @param $role
     * @param $roleClass
     */
    private function storeDatabaseToken($token, $role, $roleClass)
    {
        $this->oauthModel::create([
            'token'      => $token,
            'role_id'    => $role->id,
            'role_class' => $roleClass,
            'expired_at' => Carbon::now()->addSeconds($this->expireTime)
        ]);
    }

    /**
     * 保存缓存token
     * @param $token
     * @param $role
     * @param $roleClass
     */
    private function storeCacheToken($token, $role, $roleClass)
    {
        Cache::tags([$this->cacheTag])->put($roleClass . '@' . $role->id, $token, $this->expireTime);
    }
}