<?php

namespace App\Services;

use App\Events\ReservationCreated;
use App\Jobs\SendEmail;
use App\Models\CancellationPolicyClone;
use App\Models\DayRate;
use App\Models\Group;
use App\Models\Guest;
use App\Models\GuestClone;
use App\Models\Hotel;
use App\Models\RatePlanClone;
use App\Models\ReservationPaymentMethod;
use App\Models\RoomClone;
use App\Models\RoomTypeClone;
use App\Models\SourceClone;
use App\Models\User;
use App\Models\UserClone;
use App\Models\XRoomReservation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationService
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Reservation';

    public function checkAvailableCount($roomTypeId, $stayType)
    {
        $date = Carbon::now()->subDay(1)->format('Y-m-d');

        if ($stayType == 'day') {
            $date = Carbon::now()->startOfDay()->format('Y-m-d');
        }
        \DB::enableQueryLog();
        $reservation = DB::table('reservations')
            ->leftJoin('room_clones', 'reservations.room_clone_id', '=', 'room_clones.id')
            ->leftJoin('rooms', 'room_clones.room_id', '=', 'rooms.id')
            ->where('rooms.room_type_id', $roomTypeId)
            ->where(function ($query) use ($date) {
                $query->where('reservations.check_in', '>=', $date)
                    ->where('reservations.check_out', '>=', $date);
            })
            ->where('stay_type', $stayType)
            ->whereIn('reservations.status', ['confirmed', 'no-show','checked-in'])
            ->select('rooms.room_type_id', DB::raw('sum(case when reservations.id is not null then 1 else 0 end) as reserved'))
            ->groupBy('rooms.room_type_id')->first(['reserved']);

        // $blocks = DB::table('blocks')
        //     ->where('start_date', '<=', $date)
        //     ->where('end_date', '>=', $date)
        //     ->select('blocks.room_id')
        //     ->distinct()->pluck('room_id');

        $room_types_with_availablity = DB::table('rooms')
            // ->whereNotIn('rooms.id', $blocks)
            ->where('rooms.has_xroom', 1)
            ->where('rooms.room_type_id', $roomTypeId)
            ->select('rooms.room_type_id', DB::raw('count(*) as enabled_count'))
            ->groupBy('rooms.room_type_id')->first();
        // dd(\DB::getQueryLog());
        $has_xroom = $room_types_with_availablity != null && intval($room_types_with_availablity->enabled_count) > 0;
        $has_not_reservation = $reservation == null;

        \Log::info("" . $room_types_with_availablity->enabled_count . ' reserved ' . $has_not_reservation);

        if ($has_xroom == false) {
            return 0;
        }

        if ($has_not_reservation) {
            return $room_types_with_availablity->enabled_count;
        }

        return intval($room_types_with_availablity->enabled_count) - intval($reservation->reserved);
    }
    /***
     * xroom-s uusgej bgaa reservation
     */
    public function createReservation(XRoomReservation $xRoomReservation)
    {
        // MySQL transaction
        DB::beginTransaction();

        try {
            $stayType = $xRoomReservation->stay_type;

            $checkIn = $xRoomReservation->check_in;
            $checkOut = $xRoomReservation->check_out;

            $totalAmount = 0;

            if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut)) || stayNights($checkIn, $checkOut, true) < 1) {
                info('checkin: ' . $checkIn);
                info('checkout: ' . $checkOut);
                return [
                    'success' => false,
                    'message' => config('constants.log.error')
                ];
            }

            // Find hotel
            $hotel = Hotel::find($xRoomReservation->hotel_id);
            if ($hotel === NULL) {
                info('error hotel not found');
                return [
                    'success' => false,
                    'message' => 'Буудлын мэдээлэл олдсонгүй.',
                ];
            }

            $paymentMethod = $xRoomReservation->paymentMethod;

            // Find default user
            $user = $hotel->users()->where('is_default', true)->first();

            // Get stay nights
            $nights = stayNights($checkIn, $checkOut);

            // Create user clone
            $userClone = UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Find original source
            $source = $hotel->sources()
                ->where('is_active', true)
                ->where('name', 'xroom.mn')
                ->first();

            if ($source === NULL) {
                info('error source not found');
                return [
                    'success' => false,
                    'message' => 'Захиалгын суваг холбогдоогүй байна.',
                ];
            }

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
            $guest = Guest::firstOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'name' => 'XRoom Guest ' . $xRoomReservation->code,
                    'description' => $xRoomReservation->client_id,
                    'is_blacklist' => false,
                ]
            );

            $params = ['stayType' => $xRoomReservation->stay_type];

            $params = array_merge($params, [
                'hotelId' => $hotel->id,
                'userCloneId' => $userClone->id,
                'sourceCloneId' => $sourceClone->id,
            ]);

            // Create group
            $group = Group::firstOrCreate(
                ['number' => Group::generateUnique(), 'hotel_id' => $hotel->id]
            );

            // Find original room type
            $roomType = $hotel->roomTypes()
                ->where('id', $xRoomReservation->room_type_id)
                ->whereHas('xroomType')
                ->first();

            if (is_null($roomType)) {
                info('error room type not connected ');
                return [
                    'success' => false,
                    'message' => $xRoomReservation->room_type_id . ' холбогдоогүй байна.',
                ];
            } else {
                $dpercent = $roomType->discount_percent;
                if ($dpercent) {
                    $roomType->default_price = $roomType->default_price - ($roomType->default_price / 100) * (int) $dpercent[0];
                    $roomType->price_time = $roomType->price_time - ($roomType->price_time / 100) * (int) $dpercent[0];
                }
            }

            // If stay night not equal to 'night'
            // Don't create rate plan
            if ($stayType === 'night') {
                // Find original rate plan
                $ratePlan = $roomType
                    ->ratePlans()
                    ->where('is_online_book', 1)
                    ->first();

                if (is_null($ratePlan)) {
                    info('error rate plan not found');
                    return [
                        'success' => false,
                        'message' => 'ratePlan тохируулаагүй байна.',
                    ];
                }


                // Create room type clone
                $roomTypeClone = RoomTypeClone::create([
                    'sync_id' => $roomType->sync_id,
                    'name' => $roomType->name,
                    'short_name' => $roomType->short_name,
                    'occupancy' => $roomType->occupancy,
                    'default_price' => $roomType->default_price,
                    'price_day_use' => $roomType->price_day_use,
                    'price_time' => $roomType->price_time,
                    'price_time_count' => $roomType->price_time_count,
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

                // Create rate plan clone
                $ratePlanClone = RatePlanClone::create([
                    'name' => $ratePlan->name,
                    'is_daily' => $ratePlan->is_daily,
                    'is_ota' => $ratePlan->is_ota,
                    'is_online_book' => $ratePlan->is_online_book,
                    'non_ref' => $ratePlan->non_ref,
                    'rate_plan_id' => $ratePlan->id,
                ]);

                // Rates is default
                $rates = [];

                // Get push rates
                for ($j = 0; $j < $nights; $j++) {
                    $start = Carbon::parse($checkIn)->addDays($j)->format('Y-m-d');
                    $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                    $rate = $ratePlan->getDailyRate($start, $end);

                    if ($rate) {
                        array_push($rates, $rate);
                    } else {
                        array_push($rates, ['id' => null, 'date' => $start, 'value' => $roomType->default_price]);
                    }
                }

                // Get numberOfGuests
                $numberOfGuests = $roomType->occupancy;

                $occupancyRatePlan = null;

                // Get all occupancyRatePlan from ratePlan
                if ($numberOfGuests < $roomType->occupancy) {
                    $occupancyRatePlan = $ratePlan->occupancyRatePlans()
                        ->where('occupancy', $numberOfGuests)
                        ->where('is_active', true)
                        ->first();
                }

                // Discount
                $discount = ($occupancyRatePlan != null ? $occupancyRatePlan->discount : 0);

                // Total amount of rates
                $amount = array_sum(array_column($rates, 'value')) - ($discount * $nights);

                $data = array_merge($params, [
                    'amount' => $amount,
                    'amount_paid' => $amount,
                    'number' => $this->model::generateNumber(),
                    'numberOfGuests' => $numberOfGuests,
                    'numberOfChildren' => 0,
                    'ratePlanCloneId' => $ratePlanClone->id,
                    'roomTypeCloneId' => $roomTypeClone->id,
                    'groupId' => $group->id,
                    'checkIn' => $checkIn,
                    'checkOut' => $checkOut,
                    'arrivalTime' => Carbon::parse($checkIn)->format('H:i'),
                    'exitTime' => Carbon::parse($checkOut)->format('H:i'),
                    'statusAt' => Carbon::now(),
                    'status' => 'confirmed',
                    'postedDate' => $hotel->working_date,
                    'xroomReservationId' => $xRoomReservation->id
                ]);

                // Store new reservation
                $reservation = $this->model::create(snakeCaseKeys($data));

                // Create day rates
                foreach ($rates as $rate) {
                    $value = $rate['value'] - $discount + $reservation->childrenAmount();
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

                // Update reservation amount
                $reservation->update([
                    'amount' => $reservation->calculate(),
                ]);

                $totalAmount += $reservation->amount;
            } else {

                // Create room type clone
                $roomTypeClone = RoomTypeClone::create([
                    'name' => $roomType->name,
                    'short_name' => $roomType->short_name,
                    'occupancy' => $roomType->occupancy,
                    'default_price' => $roomType->default_price,
                    'price_day_use' => $roomType->price_day_use,
                    'price_time' => $roomType->price_time,
                    'price_time_count' => $roomType->price_time_count,
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

                // Get numberOfGuests
                $numberOfGuests = $roomType->occupancy;

                // Total amount of rates
                // $amount = $item['timePrice'];
                $amount = $roomType->price_day_use;

                $data = array_merge($params, [
                    'amount' => $amount,
                    'number' => $this->model::generateNumber(),
                    'numberOfGuests' => $numberOfGuests,
                    'numberOfChildren' => 0,
                    'roomTypeCloneId' => $roomTypeClone->id,
                    'groupId' => $group->id,
                    'checkIn' => $checkIn,
                    'checkOut' => $checkOut,
                    'arrivalTime' => Carbon::parse($checkIn)->format('H:i'),
                    'exitTime' => Carbon::parse($checkOut)->format('H:i'),
                    'statusAt' => Carbon::now(),
                    'postedDate' => $hotel->working_date,
                    'status' => 'confirmed',
                    'xroomReservationId' => $xRoomReservation->id
                ]);

                // Store new reservation
                $reservation = $this->model::create(snakeCaseKeys($data));

                $value = $amount;

                // Create day rates
                DayRate::create([
                    'date' => Carbon::parse($checkIn)->format('Y-m-d'),
                    'value' => $value,
                    'default_value' => $value,
                    'reservation_id' => $reservation->id,
                ]);

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

                // Update reservation amount
                $reservation->update([
                    'amount' => $reservation->calculate(),
                ]);

                $totalAmount = $reservation->amount;
            }

            // Payment Process
            $token = $this->generateToken();
            $resId = $this->generateResId();
            $number = 'Xroom-' . $reservation->group_id;
            $reservationPaymentMethod = null;


            // Qpay
            if ($paymentMethod == "qpay") {

                $qpayResponse = $xRoomReservation->invoice_data;

                $path = 'qpayqr/' . md5(microtime()) . '.png';

                $reservation->qpay_invoice_id = $qpayResponse['invoice_id'];
                $reservation->qpay_qrcode = $qpayResponse['qr_text'];
                $reservation->qpay_qrimage_base64 = $qpayResponse['qr_image'];
                $reservation->qpay_qrimage = $path;
                $reservation->qpay_url = $qpayResponse['qPay_shortUrl'];
                $reservation->qpay_urls = $qpayResponse['urls'];
                $reservation->resId = $resId;
                $reservation->token = $token;

                // create middle table
                $reservationPaymentMethod = ReservationPaymentMethod::create([
                    'res_id' => $resId,
                    'group_id' => $reservation->group_id,
                    'number' => $number,
                    'reservation_id' => $reservation->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $totalAmount,
                    'qpay_invoice_id' => $qpayResponse['invoice_id'],
                    'qpay_qrcode' => $qpayResponse['qr_text'],
                    'qpay_qr_text' => $qpayResponse['qr_text'],
                    'qpay_qrimage_base64' => $qpayResponse['qr_image'],
                    'qpay_qrimage' => $path,
                    'qpay_url' => $qpayResponse['qPay_shortUrl'],
                    'qpay_urls' => serialize($qpayResponse['urls']),
                    'token' => $token,
                    'hotel_id' => $hotel->id
                ]);

                $reservationPaymentMethod->save();
            }
            
            $reservation->save();

            // Commit transaction
            DB::commit();

            // Hotel and XRoom mail to send email
            if ($hotel->has_xroom && $hotel->res_email) {
                $user = new User();
                $user->email = $hotel->res_email;
                event(new ReservationCreated($group, $user, true, 'xroom'));
            }

            $reservation->totalAmount = $totalAmount;
            $reservation->resId = $resId;

            if (!is_null($reservationPaymentMethod)) {
                $reservationPaymentMethod->qpay_urls = unserialize($reservationPaymentMethod->qpay_urls) || [];
            }

            // Send confirm email
            $user = $reservation->hotel->users->pluck('email')->toArray();
            $emailData = [
                'toEmail' => $reservation->hotel->res_email,
                'bccEmails' => $user,
                'emailType' => 'newReservationXroom',
                'source' => 'xroom.mn'
            ];

            SendEmail::dispatch($emailData, $reservation);

            return [
                'success' => true,
                'group' => $group,
                'isDirect' => false,
                'resId' => $resId,
                'reservation' => $reservation,
                'reservationPaymentMethod' => $reservationPaymentMethod
            ];
        } catch (\Exception $e) {
            \Log::error('error ', $e);
            DB::rollBack();
            return [
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => config('constants.log.error'),
            ];
        }
    }

    protected function generateToken()
    {
        $token = str_random(40);
        $reservationPayment = ReservationPaymentMethod::where('token', $token)
            ->first();

        if (isset($reservationPayment)) {
            $this->generateToken();
        }

        return $token;
    }

    protected function generateResId()
    {
        $resId = (string) Str::uuid();
        $reservationPayment = ReservationPaymentMethod::where('res_id', $resId)
            ->first();

        if (isset($reservationPayment)) {
            $this->generateResId();
        }

        return $resId;
    }
}