<?php

namespace Fengxin2017\Oauth\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OringinCheck
{
    public function handle(Request $request, \Closure $next, $guard)
    {
        if (!!array_intersect(['*', $this->getOrigin($request)], $this->getAllowedOrigins($guard))) {
            return $next($request);
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * @param Request $request
     * @return array|string
     */
    private function getOrigin(Request $request)
    {
        return $request->header('origin');
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     */
    private function getAllowedOrigins($guard)
    {
        return config('jkb.guards.' . $guard . '.allowed_origins');
    }

}