<?php

namespace Fengxing2017\Oauth\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class UserGuard implements Guard

{
    use GuardHelpers;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var
     */
    protected $inputKey;

    /**
     * @var string
     */
    protected $token = 'token';

    /**
     * UserGuard constructor.
     * @param UserProvider $provider
     * @param Request|null $request
     */
    public function __construct(UserProvider $provider, Request $request = null)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = config('jkb.authorization_key', 'Authorization');
    }

    /**
     * Determine if the current user is authenticated.
     * @return bool
     */
    public function check()
    {
        return !is_null($this->user());
    }

    /**
     * Get the currently authenticated user.
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        return $this->user = $this->provider->retrieveByCredentials(
            [$this->token => $this->getTokenForRequest()]
        );
    }

    /**
     * Rules a user's credentials.
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {

    }

    /**
     * @return array|string
     */
    public function getTokenForRequest()
    {
        return $this->request->header($this->inputKey);
    }

    /**
     * Set the current request instance.
     * @param  \Illuminate\Http\Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}