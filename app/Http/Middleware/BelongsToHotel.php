<?php

namespace App\Http\Middleware;

use Closure;

class BelongsToHotel
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
        // Get slug from header
        $id = $request->header('X-Slug');
        // Get user system role
        $sysRole = $request->user()->sys_role;
        // Check user system role
        if ($sysRole != 'user' && !is_null($sysRole)) {
            // Find hotel
            $hotel = \App\Models\Hotel::where('id', $id)
                ->first();
        } else {
            // Find hotel
            $hotel = $request->user()
                ->hotels()
                ->where('hotels.id', $id)
                ->first();
                
            if (!$hotel->is_active) {
                abort(403);
            }

            if (!$hotel) {
                abort(403);
            }
        }

        // Put hotel to request
        $request->request->add([
            'hotel' => $hotel,
        ]);

        return $next($request);
    }
}
