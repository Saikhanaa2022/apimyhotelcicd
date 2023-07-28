<?php

namespace App\Http\Middleware;

use Closure;

class AccessXroom
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->header('access-key') == config('services.xroom.access_key')) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'Permission denied',
        ], 403);
    }
}
