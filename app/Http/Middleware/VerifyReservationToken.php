<?php

namespace App\Http\Middleware;

use Closure;

class VerifyReservationToken
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
        $params = $request->route()->parameters();

        if (array_key_exists('id', $params)) {
            $order = \App\Models\ReservationPaymentMethod::where('group_id', $params['id'])->firstOrFail();
            if ($request->query('token') == $order->token || $request->input('token') == $order->token) {
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'message' => 'Алдаа гарлаа. Хандах боломжгүй байна.'
            ], 400);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Алдаа гарлаа. Хандах боломжгүй байна.'
        ], 400);
    }
}
