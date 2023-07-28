<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $code = null)
    {
        // Check authenticated user has permission
        $hasPermission = $code
            ? $request->user()->hasPermission($code)
            : true;

        // Check authenticated user has hotel
        $hasOwnHotel = !!$request->hotel;

        if (!$hasPermission || !$hasOwnHotel) {
            return response()->json([
                'message' => 'Permission denied',
            ], 403);
        }

        return $next($request);
    }
}
