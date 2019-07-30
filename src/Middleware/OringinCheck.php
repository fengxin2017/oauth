<?php

namespace Jkb\Oauth\Middleware;

use Illuminate\Http\Request;

class OringinCheck
{
    public function handle(Request $request, \Closure $next)
    {
        if (in_array('*', config('jkb.allowed_origins'))) {
            return $next($request);
        }

        if (!in_array(request()->header('origin'), config('jkb.allowed_origins'))) {
            return response()->json([
                'message' => 'forbidden'
            ], 403);
        }

        return $next($request);
    }
}