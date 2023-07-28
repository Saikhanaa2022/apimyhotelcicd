<?php

namespace App\Traits;

use App\ErrorLog;
use App\Jobs\SendEmail;
use Carbon\Carbon;
use App\Models\{Hotel, Reservation, Cancellation, Guest, GuestClone, SourceClone, TaxClone, UserClone};
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

trait ReservationTrait
{
    function confirmReservation($reservationPaymentMethod, $paymentMethod)
    {
        if ($reservationPaymentMethod) {
            try {
                if ($reservationPaymentMethod->paid != 1) {
                    $reservationPaymentMethod->paid = 1;
                    $reservationPaymentMethod->save();
                    $reservations = Reservation::where('group_id', $reservationPaymentMethod->group_id)->get();
                    $userId = 0;
                    // Check reservation
                    foreach ($reservations as $reservation) {
                        $userId = $reservation->guestClone->phone_number;
                        $reservation->status = 'confirmed';
                        $reservation->amount_paid = $reservation->amount;
                        $reservation->updated_at = Carbon::now();
                        $reservation->update();
    
                        // Send confirm email
                        $user = $reservation->hotel->users->pluck('email')->toArray();
                        array_push($user, 'info@ihotel.mn', 'sales@ihotel.mn', 'finance@ihotel.mn');
                        $emailData = [
                            'toEmail' => $reservation->hotel->res_email,
                            'bccEmails' => $user,
                            'amount' => $reservationPaymentMethod->amount,
                            'payment_method' => $paymentMethod,
                            'emailType' => 'xroomConfirmReservation',
                            'source' => 'rms.myhotel.mn'
                        ];
                        SendEmail::dispatch($emailData, $reservation);
                    }
                    $this->updateXroomReservation($userId, $reservationPaymentMethod->res_id, "confirmed", 0, $reservationPaymentMethod->payment_method);
                }
            } catch (Exception $ex) {
                Log::error('Reservation confirmation failed: ', [
                    'message' => $ex->getMessage()
                ]);
            }
        }
    }

    function cancelOrder($group_id, $reservationPaymentMethod) 
    {
        $reservations = Reservation::where('group_id', $group_id)->whereHas('sourceClone', function ($query) {
            $query->where('service_name', 'xroom');
        })
        ->with(['sourceClone'])
        ->with(['cancellationPolicyClone'])
        ->get();

        DB::beginTransaction();

        try {
            $totalPenalty = 0;
            $totalBalance = 0;
            $userId = $reservations[0]->guestClone->phone_number;

            foreach($reservations as $reservation) {
                $cancellationPolicyClone = $reservation->cancellationPolicyClone;
                $cancellationPayment = $cancellationPolicyClone != null ? $cancellationPolicyClone->cancellationPayment : 0;
                $oldStatus = $reservation->status;
                $status = 'canceled';
                $reservation->payments()->where('is_active', 1)->update(['is_active' => 0]);

                $hotel = Hotel::where('id', $reservation->hotel_id)->first();

                $user = $hotel->users()->where('is_default', true)->first();

                // Create user clone
                $userClone = UserClone::create([
                    'name' => $user->name,
                    'position' => $user->position,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'user_id' => $user->id,
                ]);

                // Check existing cancellation
                if (!$reservation->is_time) {
                    if (!is_null($reservation->cancellation)) {
                        // Update cancellation
                        $reservation->cancellation->update([
                            'amount' => $cancellationPayment,
                            'is_paid' => false,
                        ]);
                    } else {
                        // Create cancellation
                        Cancellation::create([
                            'hotel_id' => $hotel->id,
                            'user_clone_id' => $userClone->id,
                            'reservation_id' => $reservation->id,
                            'amount' => $cancellationPayment,
                            'is_paid' => false,
                        ]);
                    }
                }

                $totalPenalty = $totalPenalty + $cancellationPayment;

                // Update status changed date
                if ($status !== $oldStatus) {
                    $reservation->status_at = Carbon::now();
                    $reservation->save();
                }

                $reservation->update([
                    'status' => $status,
                    'balance' => $reservation->balance
                ]);

                $totalBalance += $reservation->balance;
            }

            // Commit transaction
            DB::commit();
            if ($reservationPaymentMethod) {
                $this->updateXroomReservation($userId, $reservationPaymentMethod->res_id, "canceled", $totalBalance, $reservationPaymentMethod->payment_method);
            }
        } catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Алдаа гарлаа.' 
            ], 400);
        }
    }

    function updateXroomReservation($userId, $resId, $status, $method, $balance = 0)
    {
        $params = [
            'userId' => $userId,
            'resId' => $resId,
            'changedStatus' => $status,
            'balance' => $balance,
            'method' => $method
        ];

        try {
            $http = new \GuzzleHttp\Client;
            $http->post(config('services.xroom.api') . '/reservation/update', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $params,
            ]);
        } catch(\Exception $e) {
            Log::error('Xroom reservation update failed: ', [
                'message' => $e->getMessage()
            ]);
        }
    }

    function isReservationPossible($roomType, $checkIn, $checkOut)
    {
        try {
            $reservations_count = Reservation::where([['check_in', '=', $checkIn], ['check_out', '=', $checkOut], ['status', '!=', 'pending'], ['status', '!=', 'canceled'], ['status', '!=', 'checked-out']])
            ->whereHas('roomTypeClone', function($query) use($roomType) {
                $query->where('room_type_id', $roomType->id);
            })->count();

            if ($reservations_count >= $roomType->xroomType->sale_quantity) {
                return false;
            }
            return true;
        } catch(\Exception $e) {
            Log::error('Xroom reservation check failed: ', [
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Calculate rates.
     *
     * @param  Array  $dayRates
     * @param  Boolean $isArray
     * @return $total
     */
    function calcRates($dayRates = null, $isArray = false)
    {
        if (!$isArray) {
            $amount = $dayRates
                ? $dayRates->sum('value')
                : [];
        } else {
            $amount = array_sum(array_column($dayRates, 'value'));
        }

        return $amount;
    }
}
