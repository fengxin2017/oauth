<?php

namespace Fengxin2017\Oauth\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class UserGuard implements Guard

{
    use GuardHelpers;

    /**
     * @var Request|null
     */
    protected $request;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $inputKey;

    /**
     * 认证以哪种方式传递 header|get|post
     *
     * @var
     */
    protected $on;

    /**
     * @var string
     */
    protected $token = 'token';


    /**
     * UserGuard constructor.
     *
     * @param UserProvider $provider
     * @param Request|null $request
     *
     * @param $name
     */
    public function __construct(UserProvider $provider, Request $request = null, $name)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->on = config('jkb.auth_middleware_groups.' . $name . '.on');
        $this->inputKey = config('jkb.auth_middleware_groups.' . $name . '.authorization_key');
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        if (false === $this->validateRequest()) {
            return null;
        }

        return $this->user = $this->provider->retrieveByCredentials(
            [$this->token => $this->getTokenForRequest()]
        );
    }

    /**
     * Validate request
     *
     * @return bool
     */
    private function validateRequest()
    {
        if ($this->on == 'header' && $this->request->hasHeader($this->inputKey)) {
            return true;
        }
        if (($this->on == 'get' || $this->on == 'post') && $this->request->has($this->inputKey)) {
            return true;
        }

        return false;
    }

    /**
     * Rules a user's credentials.
     *
     * @param  array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = [])
    {

    }

    /**
     * Get token from request.
     *
     * @return mixed
     */
    public function getTokenForRequest()
    {
        if ($this->on == 'header') {
            return $this->request->header($this->inputKey);
        }
        return $this->request->{$this->inputKey};
    }

    /**
     * Set the current request instance.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}