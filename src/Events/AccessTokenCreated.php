<?php

namespace Fengxing2017\Oauth\Events;

class AccessTokenCreated
{
    /**
     * Token.
     *
     * @var string
     */
    public $token;

    /**
     * 认证模型ID.
     *
     * @var string
     */
    public $userId;

    /**
     * 驱动
     *
     * @var string
     */
    public $driver;

    /**
     * @param  string $token
     * @param  string $userId
     * @param  string $driver
     * @return void
     */
    public function __construct($token, $userId, $driver)
    {
        $this->userId = $userId;
        $this->token = $token;
        $this->driver = $driver;
    }
}