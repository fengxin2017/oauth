<?php

namespace Fengxin2017\Oauth\Facade;

use Fengxin2017\Oauth\OauthManager;
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