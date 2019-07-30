<?php

namespace JKB\Oauth\Facade;

use JKB\Oauth\OauthManager;
use Illuminate\Support\Facades\Facade;

class Oauth extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return OauthManager::class;
    }
}