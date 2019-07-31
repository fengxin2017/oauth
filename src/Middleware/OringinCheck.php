<?php

namespace Fengxing2017\Oauth\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OringinCheck
{
    /**
     * 域名验证
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if (in_array('*', config('jkb.allowed_origins'))) {
            return $next($request);
        }

        if (!in_array(request()->header('origin'), config('jkb.allowed_origins'))) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}