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
     * @var string
     */
    public $jwtKey = 'Fantastic-Taylor-Otwell';

    /**
     * @var null
     */
    public $guard = null;

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

    }

    /**
     * @param $role
     * @param $roleClass
     * @return string
     */
    public function generateToken($role, $guard)
    {
        $this->setGuard($guard);

        $this->initConfig();

        $this->initMethodName();

        $this->{$this->dropTokenMethodName}($role, get_class($role));

        return $this->createOauthToken($role, get_class($role));
    }

    /**
     * init Config
     */
    private function initConfig()
    {
        $this->dirver = config('jkb.guards.' . $this->guard . '.driver', 'database');
        $this->cacheTag = config('jkb.guards.' . $this->guard . '.cache_tag', $this->guard);
        $this->reToken = config('jkb.guards.' . $this->guard . '.retoken', true);
        $this->expireTime = Carbon::now()->addSeconds(config('jkb.guards.' . $this->guard . '.cache_expire_time'), 3600);
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
     * @param $roleClass
     */
    private function dropDatabaseToken($role, $roleClass)
    {
        if (true === $this->reToken) {
            $this->oauthModel::where('role_id', $role->id)
                ->where('role_class', $roleClass)
                ->where('guard', $this->guard)
                ->delete();
        }
    }

    /**
     * @param $role
     * @param $roleClass
     */
    private function dropCacheToken($role, $roleClass)
    {
        if (true == $this->reToken) {
            Cache::tags([$this->cacheTag])->pull($roleClass . '@' . $role->id . '@' . $this->guard);
        }
    }

    /**
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
     * @param $role
     * @param $roleClass
     * @return string
     */
    private function createDatabaseToken($role, $roleClass)
    {
        return tap(md5($roleClass . $role->id . $this->guard . time()), function ($token) use ($role) {
            Event::dispatch(new AccessTokenCreated($token, $role->id, $this->guard, 'database'));
        });
    }

    /**
     * @param $role
     * @param $roleClass
     * @return string
     */
    private function createCacheToken($role, $roleClass)
    {
        return tap(JWT::encode([
            'role'       => $role,
            'cache_key'  => $roleClass . '@' . $role->id . '@' . $this->guard,
            'role_class' => $roleClass,
            'guard'      => $this->guard,
            'time'       => time(),
        ], $this->jwtKey), function ($token) use ($role) {
            Event::dispatch(new AccessTokenCreated($token, $role->id, $this->guard, 'cache'));
        });
    }

    /**
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
            'guard'      => $this->guard,
            'expired_at' => $this->expireTime
        ]);
    }

    /**
     * @param $token
     * @param $role
     * @param $roleClass
     */
    private function storeCacheToken($token, $role, $roleClass)
    {
        Cache::tags([$this->cacheTag])->put($roleClass . '@' . $role->id . '@' . $this->guard, $token, $this->expireTime);
    }

    /**
     * @param $guard
     */
    public function setGuard($guard)
    {
        $this->guard = $guard;
    }

    public function getGuard()
    {
        return $this->guard;
    }
}