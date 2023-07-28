<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Reservation;
use Carbon\Carbon;

class CheckReservationAvailable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  String  $mode|in:related,same...
     * @return mixed
     */
    public function handle($request, Closure $next, $mode = 'related')
    {
        $id = '';
        if ($mode == 'related')
            $id = $request->filled('reservationId') ? $request->reservationId : '';
        else if ($mode == 'same')
            $id = $request->id;

        if ($id) {
            // Find reservation
            $reservation = Reservation::find($id);

            if ($reservation) {
                $status = $reservation->status;

                if ($mode === 'related' && in_array($status, ['checked-out', 'canceled', 'no-show'])) {
                    return response()->json([
                        'message' => trans('messages.reservation_not_available', ['status' => $status]),
                    ], 400);
                }

                // $workingDate = Carbon::parse($reservation->hotel->working_date);
                // if ($mode === 'same' && $workingDate->gt($reservation->check_out)) {
                //     return response()->json([
                //         'message' => trans('messages.checkout_date_passed')
                //     ], 400);
                // }

                if ($mode === 'same') {
                    $routeName = $request->route()->getName();
                    if ($routeName == 'assignRoom' && in_array($status, ['checked-out', 'canceled', 'checked-in'])) {
                        return response()->json([
                            'message' => trans('messages.reservation_not_available', ['status' => $status]),
                        ], 400);
                    }

                    if ($routeName == 'updateDiscount' && in_array($status, ['checked-out', 'canceled'])) {
                        return response()->json([
                            'message' => trans('messages.reservation_not_available', ['status' => $status]),
                        ], 400);
                    }
                }
            }
        } else {
            return response()->json([
                'message' => 'Something went wrong. Try again!'
            ], 400);
        }

        return $next($request);
    }
}
