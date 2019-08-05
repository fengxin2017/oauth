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
     * @var Request|null
     */
    protected $request;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $inputKey;

    /**
     * 守卫的名称
     * @var string
     */
    protected $name;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $shouldExcept;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $exceptHeaderKey;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $exceptHeaderLists;

    /**
     * @var string
     */
    protected $token = 'token';


    /**
     * UserGuard constructor.
     * @param UserProvider $provider
     * @param Request|null $request
     * @param $name
     */
    public function __construct(UserProvider $provider, Request $request = null, $name)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->name = $name;
        $this->inputKey = config('jkb.guards.' . $name . '.authorization_key', 'Authorization');
        $this->shouldExcept = config('jkb.guards.' . $name . '.except', null);
        $this->exceptHeaderKey = config('jkb.guards.' . $name . '.except_header_key', false);
        $this->exceptHeaderLists = config('jkb.guards.' . $name . '.except_header_lists', []);
    }

    /**
     * Determine if the current user is authenticated.
     * @return bool
     */
    public function check()
    {
        if ($this->shouldExcept()) {
            return true;
        }
        return !is_null($this->user());
    }

    /**
     * 如果开启令牌验证header头信息
     * @return bool
     */
    private function shouldExcept()
    {
        if (
            $this->shouldExcept
            && $this->exceptHeaderKey
            && in_array($this->request->header($this->exceptHeaderKey), $this->exceptHeaderLists)
        ) {
            return true;
        }
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