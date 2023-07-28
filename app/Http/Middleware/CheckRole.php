<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
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
        // $id = $request->header('X-Slug');
        // $sysRole = $request->user()->sys_role;

        // // Check authenticated user is admin
        // if ($sysRole === 'user' || is_null($sysRole)) {
        //     return response()->json([
        //         'message' => 'Permission denied',
        //     ], 403);
        // }

        // // Find hotel
        // $hotel = \App\Models\Hotel::where('id', $id)->first();

        // $request->request->add([
        //     'hotel' => $hotel,
        // ]);

        return $next($request);
    }
}
