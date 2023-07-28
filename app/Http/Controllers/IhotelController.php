<?php

namespace App\Http\Controllers;

use App\Models\{
    Reservation, Hotel, FacilityCategory, Cancellation,
    CancellationPolicyClone, DayRate, Guest, GuestClone,
    Group, RoomClone, RoomTypeClone, RatePlanClone,
    SourceClone, TaxClone, User, UserClone, ResReq, ReservedRoomType
};
use App\Events\{ReservationCreated};
use App\Jobs\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class IhotelController extends Controller
{
    /**
     * Create ihotel reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReservation(Request $request)
    {
        $data = $request->input('data');

        // Get checkIn and checkOut from request
        $checkIn = $data['checkIn'];
        $checkOut = $data['checkOut'];
        $reqGuest = $data['guest'];
        $rooms = $data['rooms'];

        // Find hotel
        $hotel = Hotel::find($data['hotelId']);
        if ($hotel) {
            try {
                // MySQL transaction
                DB::beginTransaction();

                // Find default user
                $user = $hotel->users()->where('is_default', true)->first();
                // Find online book source
                $source = $hotel->sources()
                    ->where('is_active', true)
                    ->where('service_name', 'ihotel')
                    ->first();

                // Calculate stay nights
                $nights = stayNights($checkIn, $checkOut);

                // Create user clone
                $userClone = UserClone::create([
                    'name' => $user->name,
                    'position' => $user->position,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                    'user_id' => $user->id,
                ]);

                // Create source clone
                $sourceClone = SourceClone::create([
                    'name' => $source->name,
                    'short_name' => $source->short_name,
                    'color' => $source->color,
                    'is_default' => $source->is_default,
                    'is_active' => $source->is_active,
                    'service_name' => $source->service_name,
                    'source_id' => $source->id,
                ]);

                // Original guest
                $guest = Guest::create([
                    'name' => $reqGuest['name'],
                    'surname' => $reqGuest['surname'],
                    'phone_number' => $reqGuest['phoneNumber'],
                    'email' => $reqGuest['email'],
                    'passport_number' => $reqGuest['passportNumber'],
                    'nationality' => $reqGuest['nationality'],
                    'is_blacklist' => false,
                    'hotel_id' => $hotel->id,
                ]);

                // Create group
                $group = Group::create(['number' => Group::generateUnique(), 'hotel_id' => $hotel->id]);

                $params = [
                    'hotelId' => $hotel->id,
                    'userCloneId' => $userClone->id,
                    'sourceCloneId' => $sourceClone->id,
                ];

                // Check room counts
                if (count($rooms) > 0) {
                    // Create reservations
                    foreach ($rooms as $item) {
                        // Find original room type
                        $roomType = $hotel->roomTypes()
                            ->where('id', $item['sync_id'])
                            ->first();

                        if (!is_null($roomType)) {
                            $quantity = (int) $item['room_number'];
                            $allguests = $item['guests'];

                            if ($quantity > 0) {
                                for ($i = 0; $i < $quantity; $i++) {
                                    // Create room type clone
                                    $roomTypeClone = RoomTypeClone::create([
                                        'name' => $roomType->name,
                                        'sync_id' => $roomType->sync_id,
                                        'short_name' => $roomType->short_name,
                                        'occupancy' => $roomType->occupancy,
                                        'default_price' => $roomType->default_price,
                                        'price_day_use' => $roomType->price_day_use,
                                        'has_time' => $roomType->has_time,
                                        'occupancy_children' => $roomType->occupancy_children,
                                        'has_extra_bed' => $roomType->has_extra_bed,
                                        'extra_beds' => $roomType->extra_beds,
                                        'room_type_id' => $roomType->id,
                                        'discount_percent' => $roomType->discount_percent,
                                        'is_res_request' => $roomType->is_res_request,
                                        'by_person' => $roomType->by_person,
                                        'sale_quantity' => $roomType->sale_quantity,
                                    ]);

                                    // Rates
                                    $rates = [];
                                    $blockSyncId = $item['block_sync_id'];
                                    $prices = $item['rates'];
                                    $numberOfGuests = $item['person_number'];
                                    // If by person calculate guests number
                                    if ($item['by_person']) {
                                        if ($allguests < $item['person_number']) {
                                            $numberOfGuests = $allguests;
                                        } else {
                                            $allguests = $allguests - $item['person_number'];
                                            $numberOfGuests = $item['person_number'];
                                        }
                                    }

                                    // Push rates
                                    foreach ($prices as $price) {
                                        if ($item['by_person']) {
                                            $value = (int) (($price['value'] / $item['guests']) * $numberOfGuests);
                                        } else {
                                            $value = (int) ($price['value'] / $quantity);
                                        }
                                        array_push($rates, ['id' => null, 'date' => $price['start_date'], 'value' => $value]);
                                    }

                                    // Discount
                                    $discount = 0;
                                    // Total amount of rates
                                    $amount = array_sum(array_column($rates, 'value')) - ($discount * $nights);

                                    $data = array_merge($params, [
                                        'sync_id' => $blockSyncId,
                                        'stayType' => 'night',
                                        'status' => 'pending',
                                        'amount' => $amount,
                                        'number' => Reservation::generateNumber($request),
                                        'numberOfGuests' => $numberOfGuests,
                                        'numberOfChildren' => 0,
                                        'ratePlanCloneId' => null,
                                        'roomTypeCloneId' => $roomTypeClone->id,
                                        'groupId' => $group->id,
                                        'checkIn' => $checkIn . ' ' . $hotel->check_in_time,
                                        'checkOut' => $checkOut . ' ' . $hotel->check_out_time,
                                        'arrivalTime' => $hotel->check_in_time,
                                        'exitTime' => $hotel->check_out_time,
                                        'statusAt' => Carbon::now(),
                                        'postedDate' => $hotel->working_date
                                    ]);

                                    // Store new reservation
                                    $reservation = Reservation::create(snakeCaseKeys($data));

                                    // Create day rates
                                    foreach ($rates as $rate) {
                                        $value = $rate['value'];
                                        DayRate::create([
                                            'date' => $rate['date'],
                                            'value' => $value,
                                            'default_value' => $value,
                                            'reservation_id' => $reservation->id,
                                        ]);
                                    }

                                    // Create guest clone
                                    GuestClone::create([
                                        'name' => $guest->name,
                                        'surname' => $guest->surname,
                                        'phone_number' => $guest->phone_number,
                                        'email' => $guest->email,
                                        'passport_number' => $guest->passport_number,
                                        'nationality' => $guest->nationality,
                                        'description' => $guest->description,
                                        'is_blacklist' => $guest->is_blacklist,
                                        'blacklist_reason' => $guest->blacklist_reason,
                                        'guest_id' => $guest->id,
                                        'reservation_id' => $reservation->id,
                                        'is_primary' => true,
                                    ]);

                                    // Find original cancellation policy
                                    $cancellationPolicy = $hotel->cancellationPolicy;

                                    // Create cancellation policy clone
                                    $cancellationPolicyClone = CancellationPolicyClone::create([
                                        'is_free' => $cancellationPolicy->is_free,
                                        'has_prepayment' => $cancellationPolicy->has_prepayment,
                                        'cancellation_time_id' => $cancellationPolicy->cancellation_time_id,
                                        'cancellation_percent_id' => $cancellationPolicy->cancellation_percent_id,
                                        'addition_percent_id' => $cancellationPolicy->addition_percent_id,
                                        'cancellation_policy_id' => $cancellationPolicy->id,
                                    ]);

                                    $reservation->cancellationPolicyClone()->associate($cancellationPolicyClone);
                                    $reservation->save();

                                    // Auto arrange room
                                    if ($hotel->is_auto_arrange) {
                                        $availableRooms = $reservation->availableRooms();
                                        // Get first room
                                        $firstRoom = $availableRooms->first();

                                        // Create room clone
                                        if ($firstRoom) {
                                            $roomClone = RoomClone::create([
                                                'name' => $firstRoom->name,
                                                'ic_name' => $firstRoom->ic_name,
                                                'status' => $firstRoom->status,
                                                'description' => $firstRoom->description,
                                                'room_id' => $firstRoom->id,
                                            ]);

                                            $reservation->roomClone()->associate($roomClone);
                                            $reservation->save();
                                        }
                                    }

                                    // Sync taxes to reservation taxes
                                    foreach ($hotel->taxes as $tax) {
                                        if ($tax->is_enabled) {
                                            // Create tax clones for reservation
                                            $data = TaxClone::create([
                                                'name' => $tax->name,
                                                'percentage' => $tax->percentage,
                                                'inclusive' => true,
                                                'key' => $tax->key,
                                                'is_default' => $tax->is_default,
                                                'is_enabled' => $tax->is_enabled,
                                                'reservation_id' => $reservation->id,
                                                'tax_id' => $tax->id,
                                            ]);
                                            // if (!$data->inclusive) {
                                            //     // Update reservation amount
                                            //     $reservation = $data->reservation;
                                            //     $reservation->update([
                                            //         'amount' => $reservation->calculate(),
                                            //     ]);
                                            // }
                                        }
                                    }
                                }
                            } else {
                                return response()->json([
                                    'status' => false,
                                    'message' => 'Алдаа гарлаа. Өрөөний тоо хэмжээ буруу байна.',
                                ]);
                            }
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' => 'Алдаа гарлаа. Өрөөний бүртгэл олдсонгүй.',
                            ]);
                        }
                    }

                    // Commit transaction
                    DB::commit();

                    // Send email to hotel reservation email
                    if (config('app.env') === 'production' && $hotel->res_email) {
                        $user = new User;
                        $user->email = $hotel->res_email;
                        event(new ReservationCreated($group, $user, true, 'ihotel'));
                    }

                    return response()->json([
                        'status' => true,
                        'syncId' => $group->id,
                        'message' => 'Захиалга амжилттай бүртгэгдлээ.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Өрөөний мэдээлэл оруулна уу.'
                    ]);
                }
            } catch (Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
        ]);
    }

    /**
     * Update ihotel reservation with date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReservationDate(Request $request)
    {
        // Get data from request
        $data = $request->input('data');
        $syncId = $data['syncId'];
        $status = $data['status'];
        $startdate = $data['startdate'];
        $enddate = $data['enddate'];

        // Find hotel
        $hotel = Hotel::find($data['hotelId']);
        if ($hotel) {
            try {
                // Get reservations by group id
                $reservations = Reservation::where('group_id', $syncId)->get();

                if (count($reservations) > 0) {
                    foreach ($reservations as $reservation) {
                        $reservation->status = $status;
                        $reservation->status_at = Carbon::now();
                        $reservation->check_in = Carbon::parse($startdate . $reservation->arrival_time)->format('Y-m-d H:i:s');
                        $reservation->check_out = Carbon::parse($enddate . $reservation->exit_time)->format('Y-m-d H:i:s');
                        $reservation->update();

                        if ($status === 'canceled') {
                            // If cancel reservation then is active = false payments
                            $reservation->payments()->where('is_active', 1)->update(['is_active' => 0]);

                            // Check existing cancellation
                            if (!is_null($reservation->cancellation)) {
                                // Update cancellation
                                $reservation->cancellation->update([
                                    'amount' => 0,
                                    'is_paid' => false,
                                ]);
                            } else {
                                // Create cancellation
                                Cancellation::create([
                                    'hotel_id' => $hotel->id,
                                    'user_clone_id' => $reservation->user_clone_id,
                                    'reservation_id' => $reservation->id,
                                    'amount' => 0,
                                    'is_paid' => false,
                                ]);
                            }
                        }

                        if ($status === 'confirmed' && !is_null($reservation->cancellation)) {
                            $reservation->payments()->where('is_active', 0)->update(['is_active' => 1]);
                            $reservation->cancellation->delete();
                        }
                    }

                    return response()->json([
                        'status' => true,
                        'syncId' => $reservation->id,
                        'message' => 'Захиалга амжилттай шинэчлэгдлээ.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Захиалгын бүртгэл үүсээгүй байна.'
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
        ]);
    }

    /**
     * Update ihotel reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReservation(Request $request)
    {
        // Get data from request
        $data = $request->input('data');
        $syncId = $data['syncId'];
        $status = $data['status'];

        // Find hotel
        $hotel = Hotel::find($data['hotelId']);
        if ($hotel) {
            try {
                // Get reservations by group id
                $reservations = Reservation::where('group_id', $syncId)->get();

                if (count($reservations) > 0) {
                    foreach ($reservations as $reservation) {
                        $reservation->status = $status;
                        $reservation->status_at = Carbon::now();
                        $reservation->update();

                        if ($status === 'canceled') {
                            // If cancel reservation then is active = false payments
                            $reservation->payments()->where('is_active', 1)->update(['is_active' => 0]);

                            // Check existing cancellation
                            if (!is_null($reservation->cancellation)) {
                                // Update cancellation
                                $reservation->cancellation->update([
                                    'amount' => 0,
                                    'is_paid' => false,
                                ]);
                            } else {
                                // Create cancellation
                                Cancellation::create([
                                    'hotel_id' => $hotel->id,
                                    'user_clone_id' => $reservation->user_clone_id,
                                    'reservation_id' => $reservation->id,
                                    'amount' => 0,
                                    'is_paid' => false,
                                ]);
                            }
                        }

                        if ($status === 'confirmed' && !is_null($reservation->cancellation)) {
                            $reservation->payments()->where('is_active', 0)->update(['is_active' => 1]);
                            $reservation->cancellation->delete();
                        }
                    }

                    return response()->json([
                        'status' => true,
                        'syncId' => $reservation->id,
                        'message' => 'Захиалга амжилттай шинэчлэгдлээ.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Захиалгын бүртгэл үүсээгүй байна.'
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
        ]);
    }

    /**
     * Create ihotel reservation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createResRequest(Request $request)
    {
        // Get data from request
        $data = $request->input('data');

        // Find hotel using synced hotel id in ihotel
        $hotel = Hotel::find($data['hotelId']);

        if ($hotel) {
            try {
                // Get values from data
                $reqGuest = $data['guest'];
                $stayNights = $data['stayNights'];
                $amount = $data['amount'];
                $numberOfGuests = $data['numberOfGuests'];
                $numberOfRooms = $data['numberOfRooms'];
                $syncId = $data['resSyncId'];
                $discountCalcType = $data['discountCalcType'];
                $discountAvgPercent = $data['discountAvgPercent'];
                $rooms = $data['rooms'];
                $notes = $data['notes'];
                $checkIn = $data['checkIn'];
                $checkOut = $data['checkOut'];
                // MySQL transaction begin
                DB::beginTransaction();

                // Find ihotel source
                $source = $hotel->sources()
                    ->where('is_active', true)
                    ->where('service_name', 'ihotel')
                    ->first();

                // Create source clone
                $sourceClone = SourceClone::create([
                    'name' => $source->name,
                    'short_name' => $source->short_name,
                    'color' => $source->color,
                    'is_default' => $source->is_default,
                    'is_active' => $source->is_active,
                    'service_name' => $source->service_name,
                    'source_id' => $source->id,
                ]);

                // Create guest
                $guest = Guest::create([
                    'name' => $reqGuest['name'],
                    'surname' => $reqGuest['surname'],
                    'phone_number' => $reqGuest['phoneNumber'],
                    'email' => $reqGuest['email'],
                    'passport_number' => $reqGuest['passportNumber'],
                    'nationality' => $reqGuest['nationality'],
                    'is_blacklist' => false,
                    'hotel_id' => $hotel->id,
                ]);

                // Create reservation request
                $resReq = ResReq::create(snakeCaseKeys([
                    'resNumber' => ResReq::generateNumber(),
                    'status' => 'pending',
                    'stayType' => 'night',
                    'stayNights' => $stayNights,
                    'numberOfGuests' => $numberOfGuests,
                    'numberOfChildren' => 0,
                    'amount' => $amount,
                    'discountCalcType' => $discountCalcType,
                    'discountAvgPercent' => $discountAvgPercent,
                    'guest' => json_encode($guest, JSON_UNESCAPED_UNICODE),
                    'numberOfRooms' => $numberOfRooms,
                    'sync_id' => $syncId,
                    'hotelId' => $hotel->id,
                    'sourceCloneId' => $sourceClone->id,
                    'notes' => $notes,
                    'checkIn' => $checkIn,
                    'checkOut' => $checkOut
                ]));

                // Create reserved room types
                foreach ($rooms as $item) {
                    // Find original room type
                    $roomType = $hotel->roomTypes()
                        ->where('id', $item['sync_id'])
                        ->first();

                    if ($roomType) {
                        $quantity = (int) $item['room_number'];

                        if ($quantity > 0) {
                            $rates = $item['preRates'];
                            $roomAmount = (int) $item['amount'];

                            ReservedRoomType::create([
                                'reservation_request_id' => $resReq->id,
                                'room_type_id' => $roomType->id,
                                'sync_id' => $roomType->sync_id,
                                'name' => $roomType->name,
                                'short_name' => $roomType->short_name,
                                'occupancy' => $item['person_number'],
                                'quantity' => $quantity,
                                'by_person' => $item['by_person'],
                                'number_of_guests' => $item['guests'],
                                'rates' => json_encode($rates, JSON_UNESCAPED_UNICODE),
                                'amount' => $roomAmount
                            ]);
                        } else {
                            return response()->json([
                                'status' => false,
                                'message' => 'Алдаа гарлаа. Өрөөний тоо хэмжээ буруу байна.',
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Алдаа гарлаа. Өрөөний бүртгэл олдсонгүй.',
                        ]);
                    }
                }

                // Commit transaction
                DB::commit();

                // Send email to hotel reservation email
                if ($hotel->res_email) {
                    $emailData = [
                        'toEmail' => $hotel->res_email,
                        'bccEmails' => $hotel->hotelSetting->bcc_emails,
                        'emailType' => 'resReqCreated',
                        'source' => 'iHotel.mn',
                    ];

                    // Trigger event
                    SendEmail::dispatch($emailData, $resReq);
                }

                return response()->json([
                    'status' => true,
                    'syncId' => $resReq->id,
                    'message' => 'Захиалга амжилттай бүртгэгдлээ.'
                ]);
            } catch (Exception $e) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
        ]);
    }

    /**
     * Update ihotel reservation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateResRequest(Request $request)
    {
        // Get data from request
        $data = $request->input('data');
        $syncId = $data['syncId'];
        $status = $data['status'];
        $hotelId = $data['hotelId'];

        // Find hotel
        $hotel = Hotel::find($hotelId);
        if ($hotel) {
            try {
                // Get reservations by group id
                $resReq = ResReq::find($syncId);

                if (!is_null($resReq)) {
                    $resReq->status = $status;
                    // Update status
                    $resReq->update();

                    if ($status == 'confirmed' && $hotel->res_email) {
                        // Send email to hotel reservation email
                        $emailData = [
                            'toEmail' => $hotel->res_email,
                            'bccEmails' => $hotel->hotelSetting->bcc_emails,
                            'emailType' => 'resReqConfirmed',
                            'source' => 'iHotel.mn',
                        ];

                        // Trigger event
                        SendEmail::dispatch($emailData, $resReq);
                    }

                    return response()->json([
                        'status' => true,
                        'syncId' => $resReq->id,
                        'message' => 'Захиалга амжилттай шинэчлэгдлээ.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Захиалгын бүртгэл үүсээгүй байна.'
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Буудлын бүртгэл үүсээгүй байна.',
        ]);
    }

    /**
     * Change payment of ihotel reservation request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePaymentResRequest(Request $request)
    {
        // Get data from request
        $data = $request->input('data');
        $syncId = $data['syncId'];
        $hotelId = $data['hotelId'];
        $amountPaid = $data['amountPaid'];
        $commission = $data['commission'];

        // Find hotel
        $hotel = Hotel::find($hotelId);
        if ($hotel) {
            try {
                // Get reservations by group id
                $resReq = ResReq::find($syncId);

                if (!is_null($resReq)) {
                    $resReq->is_paid = true;
                    $resReq->amount_paid = $amountPaid;
                    $resReq->commission = $commission;
                    $resReq->paid_at = Carbon::now();

                    // Update status
                    $resReq->update();

                    if ($hotel->res_email) {
                        // Send email to hotel reservation email
                        $emailData = [
                            'toEmail' => $hotel->res_email,
                            'bccEmails' => $hotel->hotelSetting->bcc_emails,
                            'emailType' => 'resReqPaid',
                            'source' => 'iHotel.mn',
                        ];

                        // Trigger event
                        SendEmail::dispatch($emailData, $resReq);
                    }

                    return response()->json([
                        'status' => true,
                        'syncId' => $resReq->id,
                        'message' => 'Үйлдэл амжилттай.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Үйлдэл амжилтгүй. Захиалгын холболт хийгдээгүй байна.'
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
        ]);
    }

    /**
     * Check reservation request availability for create main reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkResRequest(Request $request)
    {
        // Get data from request
        $data = $request->input('data');
        // Get dates
        $checkIn = $data['checkIn'];
        $checkOut = $data['checkOut'];
        // Get synced ids
        $syncId = $data['syncId'];
        $hotelId = $data['hotelId'];

        // Find hotel
        $hotel = Hotel::find($hotelId);

        // Check hotel is synced
        if ($hotel) {
            try {
                $isAvailable = true;
                // Get reservation request by id
                $resReq = ResReq::find($syncId);

                // Check reservation request room types availability
                if (!is_null($resReq)) {
                    $roomTypesIds = $resReq->reservedRoomTypes->pluck('room_type_id');

                    // Get roomTypes with available rooms count
                    $roomTypes = $hotel->roomTypes()
                        ->whereIn('id', $roomTypesIds)
                        ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                            $query->unassigned($checkIn, $checkOut);
                        }])
                        ->get();

                    foreach ($roomTypes as $roomType) {
                        // Get reserved room type
                        $rrt = $resReq->reservedRoomTypes()->where('room_type_id', $roomType->id)->first();
                        // Calc reserved count for needed
                        $reservedCount = $resReq->stay_nights * $rrt->quantity;

                        if ($roomType->rooms_count < $reservedCount) {
                            $isAvailable = false;
                        }
                    }

                    return response()->json([
                        'status' => true,
                        'isAvailable' => $isAvailable,
                        'message' => $isAvailable ? 'Захиалга үүсгэх боломжтой.' : 'Захиалга үүсгэх боломжгүй байна.'
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Үйлдэл амжилтгүй. Захиалгад холболт хийгдээгүй байна.'
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Холболт хийгдээгүй байна.',
        ]);
    }

    /**
     * Select date of ihotel reservation request.
     * Then create main reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectDateResRequest(Request $request)
    {
        // Get dates
        $checkIn = $request->input('checkIn');
        $checkOut = $request->input('checkOut');
        // Get synced ids
        $syncId = $request->input('syncId');
        $hotelId = $request->input('hotelId');
        $blockSyncIds = $request->input('blockSyncIds');
        $isUpdated = $request->input('isUpdated');

        // Find hotel
        $hotel = Hotel::find($hotelId);

        // Check hotel is synced
        if ($hotel) {
            try {
                // Get reservation request by id
                $resReq = ResReq::find($syncId);

                // Check reservation request
                if (!is_null($resReq)) {
                    $result = null;

                    // Check action
                    if ($isUpdated) {
                        // Update main reservations
                        $result = $this->updateResFromReq(
                            [
                                'checkIn' => $checkIn,
                                'checkOut' => $checkOut
                            ],
                            $resReq
                        );
                    } else {
                        // Create main reservation
                        $result = $this->createResFromReq(
                            [
                                'checkIn' => $checkIn,
                                'checkOut' => $checkOut,
                                'guest' => json_decode($resReq->guest),
                                'blockSyncIds' => $blockSyncIds
                            ],
                            $resReq
                        );
                    }

                    $isSuccess = $result['status'];

                    // Check status
                    if ($isSuccess) {
                        // Update status and dates
                        $resReq->check_in = $checkIn;
                        $resReq->check_out = $checkOut;
                        $resReq->status = 'reservation';
                        $resReq->update();
                    }

                    return response()->json([
                        'status' => $isSuccess,
                        'syncId' => $isSuccess ? $result['syncId'] : null,
                        'message' => $result['message']
                    ], $isSuccess ? 200 : 400);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Үйлдэл амжилтгүй. Захиалга холболт хийгдээгүй байна.'
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Алдаа гарлаа. Холболт хийгдээгүй байна.',
        ]);
    }

    /**
     * Create main reservation from request
     *
     * @param  \Illuminate\Http\Array $data
     * @param  $object
     * @return \Illuminate\Http\Array
     */
    private function createResFromReq($data, $object) {
        // Get data
        $checkIn = $data['checkIn'];
        $checkOut = $data['checkOut'];
        $reqGuest = $data['guest'];
        $blockSyncIds = $data['blockSyncIds'];

        $rooms = $object->reservedRoomTypes;
        $hotel = $object->hotel;

        try {
            // MySQL transaction
            DB::beginTransaction();

            // Find default user
            $user = $hotel->users()->where('is_default', true)->first();

            // Find online book source
            $source = $hotel->sources()
                ->where('is_active', true)
                ->where('service_name', 'ihotel')
                ->first();

            // Calculate stay nights
            $nights = stayNights($checkIn, $checkOut);

            // Create user clone
            $userClone = UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Create source clone
            $sourceClone = SourceClone::create([
                'name' => $source->name,
                'short_name' => $source->short_name,
                'color' => $source->color,
                'is_default' => $source->is_default,
                'is_active' => $source->is_active,
                'service_name' => $source->service_name,
                'source_id' => $source->id,
            ]);

            // Find or Create primary guest
            $guest = Guest::firstOrCreate(
                [
                    'email' => $reqGuest->email,
                    'phone_number' => $reqGuest->phone_number,
                    'hotel_id' => $hotel->id,
                ],
                [
                    'name' => $reqGuest->name,
                    'surname' => $reqGuest->surname,
                    'phone_number' => $reqGuest->phone_number,
                    'email' => $reqGuest->email,
                    'passport_number' => $reqGuest->passport_number,
                    'nationality' => $reqGuest->nationality,
                    'is_blacklist' => false,
                    'hotel_id' => $hotel->id,
                ]);

            // Create group
            $group = Group::create(['number' => Group::generateUnique(), 'hotel_id' => $hotel->id]);

            $params = [
                'hotelId' => $hotel->id,
                'userCloneId' => $userClone->id,
                'sourceCloneId' => $sourceClone->id,
            ];

            // Check room counts
            if (count($rooms) > 0) {
                // Create reservations
                foreach ($rooms as $item) {
                    // Find original roomType
                    $roomType = $item->roomType;
                    $quantity = $item->quantity;

                    // Check roomType
                    if ($roomType) {
                        if ($quantity > 0) {
                            $numberOfGuests = $item->number_of_guests;
                            $lastGuestsCount = 0;

                            // Find last reservation number of guests
                            if ($numberOfGuests / $quantity > 0) {
                                $lastGuestsCount = $numberOfGuests - ($item->occupancy * ($quantity - 1));
                            }

                            for ($i = 0; $i < $quantity; $i++) {
                                // Create room type clone
                                $roomTypeClone = RoomTypeClone::create([
                                    'name' => $roomType->name,
                                    'sync_id' => $roomType->sync_id,
                                    'short_name' => $roomType->short_name,
                                    'occupancy' => $roomType->occupancy,
                                    'default_price' => $roomType->default_price,
                                    'price_day_use' => $roomType->price_day_use,
                                    'has_time' => $roomType->has_time,
                                    'occupancy_children' => $roomType->occupancy_children,
                                    'has_extra_bed' => $roomType->has_extra_bed,
                                    'extra_beds' => $roomType->extra_beds,
                                    'room_type_id' => $roomType->id,
                                    'discount_percent' => $roomType->discount_percent,
                                    'is_res_request' => $roomType->is_res_request,
                                    'by_person' => $roomType->by_person,
                                    'sale_quantity' => $roomType->sale_quantity,
                                ]);

                                // Rates
                                $rates = [];

                                $checkRoomTypeId = $item->sync_id;
                                // Find block id
                                $createdBlock = collect($blockSyncIds)->first(function ($item) use ($checkRoomTypeId) {
                                    return $item['room_id'] == $checkRoomTypeId;
                                });

                                $blockSyncId = $createdBlock['id'];
                                $prices = json_decode($item->rates);

                                $guestsCount = $item->occupancy;
                                if ($i == $quantity - 1 &&  $lastGuestsCount > 0) {
                                    $guestsCount = $lastGuestsCount;
                                }

                                // Push rates
                                foreach ($prices as $price) {
                                    if ($item->by_person) {
                                        $value = (int) (($price->amount / $numberOfGuests) * $guestsCount);
                                    } else {
                                        $value = (int) ($price->amount / $quantity);
                                    }
                                    $start = Carbon::parse($checkIn)->addDays($price->dayNumber)->format('Y-m-d');
                                    array_push($rates, ['id' => null, 'date' => $start, 'value' => $value]);
                                }

                                // Total amount of rates
                                $amount = array_sum(array_column($rates, 'value'));

                                $data = array_merge($params, [
                                    'sync_id' => $blockSyncId,
                                    'stayType' => 'night',
                                    'res_req_id' => $object->id,
                                    'status' => 'confirmed',
                                    'amount' => $amount,
                                    'number' => Reservation::generateNumber(),
                                    'numberOfGuests' => $guestsCount,
                                    'numberOfChildren' => 0,
                                    'ratePlanCloneId' => null,
                                    'roomTypeCloneId' => $roomTypeClone->id,
                                    'groupId' => $group->id,
                                    'checkIn' => $checkIn . ' ' . $hotel->check_in_time,
                                    'checkOut' => $checkOut . ' ' . $hotel->check_out_time,
                                    'arrivalTime' => $hotel->check_in_time,
                                    'exitTime' => $hotel->check_out_time,
                                    'statusAt' => Carbon::now(),
                                    'postedDate' => $hotel->working_date,
                                ]);

                                // Store new reservation
                                $reservation = Reservation::create(snakeCaseKeys($data));

                                // Create day rates
                                foreach ($rates as $rate) {
                                    $value = $rate['value'];
                                    DayRate::create([
                                        'date' => $rate['date'],
                                        'value' => $value,
                                        'default_value' => $value,
                                        'reservation_id' => $reservation->id,
                                    ]);
                                }

                                // Create guest clone
                                GuestClone::create([
                                    'name' => $guest->name,
                                    'surname' => $guest->surname,
                                    'phone_number' => $guest->phone_number,
                                    'email' => $guest->email,
                                    'passport_number' => $guest->passport_number,
                                    'nationality' => $guest->nationality,
                                    'description' => $guest->description,
                                    'is_blacklist' => $guest->is_blacklist,
                                    'blacklist_reason' => $guest->blacklist_reason,
                                    'guest_id' => $guest->id,
                                    'reservation_id' => $reservation->id,
                                    'is_primary' => true,
                                ]);

                                $reservation->save();

                                // Auto arrange room
                                // if ($hotel->is_auto_arrange) {
                                $availableRooms = $reservation->availableRooms();
                                // Get first room
                                $firstRoom = $availableRooms->first();

                                // Create room clone
                                if ($firstRoom) {
                                    $roomClone = RoomClone::create([
                                        'name' => $firstRoom->name,
                                        'ic_name' => $firstRoom->ic_name,
                                        'status' => $firstRoom->status,
                                        'description' => $firstRoom->description,
                                        'room_id' => $firstRoom->id,
                                    ]);

                                    $reservation->roomClone()->associate($roomClone);
                                    $reservation->save();
                                }
                                // }

                                // Sync taxes to reservation taxes
                                foreach ($hotel->taxes as $tax) {
                                    if ($tax->is_enabled) {
                                        // Create tax clones for reservation
                                        $data = TaxClone::create([
                                            'name' => $tax->name,
                                            'percentage' => $tax->percentage,
                                            'inclusive' => true,
                                            'key' => $tax->key,
                                            'is_default' => $tax->is_default,
                                            'is_enabled' => $tax->is_enabled,
                                            'reservation_id' => $reservation->id,
                                            'tax_id' => $tax->id,
                                        ]);
                                        // if (!$data->inclusive) {
                                        //     // Update reservation amount
                                        //     $reservation = $data->reservation;
                                        //     $reservation->update([
                                        //         'amount' => $reservation->calculate(),
                                        //     ]);
                                        // }
                                    }
                                }
                            }
                        } else {
                            return [
                                'status' => false,
                                'message' => 'Алдаа гарлаа. Өрөөний тоо хэмжээ буруу байна.',
                            ];
                        }
                    } else {
                        return [
                            'status' => false,
                            'message' => 'Алдаа гарлаа. Өрөөний бүртгэл олдсонгүй.',
                        ];
                    }
                }

                // Commit transaction
                DB::commit();

                return [
                    'status' => true,
                    'syncId' => $group->id,
                    'message' => 'Захиалга амжилттай бүртгэгдлээ.'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Өрөөний мэдээлэл алдаатай байна.'
                ];
            }
        }  catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update reservation from request
     *
     * @param  \Illuminate\Http\Array $data
     * @param  $object
     * @return \Illuminate\Http\Array
     */
    private function updateResFromReq($data, $object) {
        try {
            // Get data
            $hotel = $object->hotel;
            $checkIn = $data['checkIn'];
            $checkOut = $data['checkOut'];

            $reservations = $object->reservations;

            // Check reservations count
            if (count($reservations) > 0) {
                foreach ($reservations as $res) {
                    $res->check_in = $checkIn . ' ' . $res->arrival_time;
                    $res->check_out = $checkOut . ' ' . $res->exit_time;
                    $res->update();

                    // Fix day rates
                    $dayRates = $res->dayRates;
                    foreach ($dayRates as $key => $item) {
                        $item->date = Carbon::parse($res->check_in)->addDays($key)->format('Y-m-d');
                        $item->save();
                    }
                }

                return [
                    'status' => true,
                    'syncId' => null,
                    'message' => 'Захиалга өдөр сонголт амжилттай хадгалагдлаа.'
                ];
            }

            return [
                'status' => false,
                'message' => 'Reservations not found.'
            ];
        }  catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
