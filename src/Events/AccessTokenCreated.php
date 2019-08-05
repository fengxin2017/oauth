<?php

namespace Fengxing2017\Oauth\Events;

class AccessTokenCreated
{
    /**
     * 认证模型ID.
     *
     * @var string
     */
    public $userId;

    /**
     * Token.
     *
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $guard;

    /**
     * 驱动
     *
     * @var string database|cache
     */
    public $driver;

    /**
     * @param  string $token
     * @param  string $userId
     * @param  string $guard
     * @param  string $driver
     * @return void
     */
    public function __construct($token, $userId, $guard, $driver)
    {
        $this->userId = $userId;
        $this->token = $token;
        $this->guard = $guard;
        $this->driver = $driver;
    }
}