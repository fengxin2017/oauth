<?php

namespace Fengxin2017\Oauth\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OringinCheck
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @param $name
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, $name)
    {
        if (!!array_intersect(['*', $this->getOrigin($request)], $this->getAllowedOrigins($name))) {
            return $next($request);
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @param Request $request
     *
     * @return array|string
     */
    private function getOrigin(Request $request)
    {
        return $request->header('origin');
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    private function getAllowedOrigins($name)
    {
        return config('jkb.auth_middleware_groups.' . $name . '.allowed_origins');
    }

}