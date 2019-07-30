<?php

namespace Jkb\Oauth\Facade;

use Jkb\Oauth\OauthManager;
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