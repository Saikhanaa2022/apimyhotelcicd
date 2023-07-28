<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;

// use App\Exports\ReservationsExport;
// use Maatwebsite\Excel\Facades\Excel;
use App\Models\{Source, SourceClone, Partner, PartnerClone, Cancellation, CancellationPolicy, CancellationPolicyClone, Child, ChildrenPolicy, Guest, GuestClone, RoomTypeClone, Room, RoomClone, RatePlanClone, User, UserClone, DayRate, Group, Item, Service, ServiceClone, ServiceCategory, ServiceCategoryClone, Tax, TaxClone};
use App\Events\{ReservationCreated, ReservationUpdated, ReservationEmailSend};

class ReservationController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Reservation';
    protected $request = 'App\Http\Requests\ReservationRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->reservations();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($query, $request)
    {
        // Get filter type from request
        if ($request->filled('filterType')) {
            $filterType = $request->input('filterType');
            if ($filterType === 'createdAt') {
                $filterField = 'created_at';
            } else if ($filterType === 'checkIn') {
                $filterField = 'check_in';
            } else if ($filterType === 'checkOut') {
                $filterField = 'check_out';
            } else {
                $filterField = '';
            }

            $hasStartDate = $request->filled('startDate');
            $hasEndDate = $request->filled('endDate');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            if ($hasStartDate && $hasEndDate) {
                $query
                    ->whereDate($filterField, '>=', date($startDate))
                    ->whereDate($filterField, '<=', date($endDate));
            } else if ($hasStartDate && !$hasEndDate) {
                $query->whereDate($filterField, '>=', date($startDate));
            } else if (!$hasStartDate && $hasEndDate) {
                $query->whereDate($filterField, '<=', date($endDate));
            }
        }

        if ($request->filled('statuses')) {
            $query->whereIn('status', $request->input('statuses'));
        }

        if ($request->has('isResRequest') && $request->input('isResRequest') === 'true') {
            $query->whereNotNull('res_req_id');
        }

        if ($request->has('isResNotPaid') && $request->input('isResNotPaid') === 'true') {
            $query->whereRaw('amount != amount_paid');
            // ->where(DB::raw('(amount - amount_paid)'), '>', 0);
        }

        return $query;
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        return $request->only([
            'id', 'arrival_time', 'notes', 'status',
        ]);
    }

    /**
     * Before create new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function beforeCommit(Request $request)
    {
        // Check dates, check balance, check room is assigned, if assigned unassign
    }

    /**
     * Store or update the resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'saveRules');

        // Get resarvation object
        $reservation = $this->newQuery()->findOrFail($request->input('id'));

        // Get reservation dates
        $checkIn = $reservation->check_in;
        $checkOut = $reservation->check_out;

        // Status is filled
        if ($request->filled('status')) {
            // Get requested and old status
            $status = $request->input('status');
            $oldStatus = $request->input('oldStatus');

            // If guest check in date is not today
            if ($status === 'checked-in') {
                $roomId = $reservation->roomClone->room_id;

                if (is_null($roomId)) {
                    return response()->json([
                        'message' => 'Захиалга өрөөнд хуваарилна уу.',
                    ], 400);
                }

                // if ($checkIn > Carbon::now()->format('Y-m-d H:i')) {
                //     return response()->json([
                //         'message' => 'Зочны ирэх хугацаа болоогүй байна.',
                //     ], 400);
                // }

                $isRoom = $this->newQuery()
                    ->where('status', 'checked-in')
                    ->where('check_out', $checkIn)
                    ->whereHas('roomClone', function ($query) use ($roomId) {
                        $query->where('room_id', $roomId);
                    })
                    ->count();

                if ($isRoom >= 1) {
                    return response()->json([
                        'message' => 'Өмнөх зочин байрлаж байна.',
                    ], 400);
                }
            }

            // If guest checked out then change room status to dirty
            if ($status === 'checked-out') {
                $roomId = $reservation->roomClone->room_id;
                $room = Room::find($roomId);
                if (is_null($room)) {
                    return response()->json([
                        'message' => 'Тус захиалгад өрөө хуваарилаагүй байна.',
                    ], 400);
                }
                $room->status = 'dirty';
                $room->save();
            }

            // If reservation status is canceled
            if ($oldStatus === 'canceled' && $status !== 'canceled') {
                // Is room free
                $isRoomFree = false;

                if ($reservation->roomClone->default) {
                    // Rooms unassigned
                    $isRoomFree =  $reservation->roomTypeClone->roomType->rooms()->unassigned($checkIn, $checkOut, $reservation->id)->exists();
                } else {
                    // Room unassigned
                    $isRoomFree = $reservation->roomClone
                        ->room()
                        ->unassigned($checkIn, $checkOut, $reservation->id)
                        ->exists();
                }

                // If rooms true
                if (!$isRoomFree) {
                    return response()->json([
                        'message' => 'Захиалгыг буцаах боломжгүй.',
                    ], 400);
                }
            }

            if ($status === 'canceled') {
                // If cancel reservation then is active = false payments
                $reservation->payments()->where('is_active', 1)->update(['is_active' => 0]);

                // Find original user
                $user = $request->user();

                $hotelId = $request->hotel->id;

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
                            'amount' => $request->input('cancellationPayment'),
                            'is_paid' => $request->input('isPaid'),
                        ]);
                    } else {
                        // Create cancellation
                        Cancellation::create([
                            'hotel_id' => $hotelId,
                            'user_clone_id' => $userClone->id,
                            'reservation_id' => $request->input('id'),
                            'amount' => $request->input('cancellationPayment'),
                            'is_paid' => $request->input('isPaid'),
                        ]);
                    }
                }
            }

            if ($status && $status !== 'canceled') {
                if ($reservation->cancellation && !$reservation->is_time) {
                    $reservation->cancellation->delete();
                }

                // If return cancel reservation then is active = false payments
                $reservation->payments()->where('is_active', 0)->update(['is_active' => 1]);
            }

            // Update status changed date
            if ($status !== $oldStatus) {
                $reservation->status_at = Carbon::now();
                $reservation->save();
            }
        }

        $data = $this->storeOrUpdate($this->requestParams($request));

        $this->afterCommit($request, $data);

        return $this->responseJSON($data);
    }

    /**
     * After new resource created.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function afterCommit(Request $request, $model)
    {
        $hotel = $model->hotel;
        // Check hotel for sync
        if ($hotel->has_ihotel && !is_null($hotel->sync_id)) {
            // Sync reservation
            event(new ReservationUpdated([$model]));
        }
    }

    /**
     * Store the resources in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveMultiple(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'saveMultipleRules');

        // MySQL transaction
        DB::beginTransaction();

        try {
            $checkIn = $request->input('checkIn');
            $checkOut = $request->input('checkOut');
            $stayType = $request->input('stayType', 'night');
            $isSendEmail = $request->input('isSendEmail', false);
            $isAutoFill = $request->input('isAutoFill', false);

            if ($isAutoFill) {
                $checkIn = $this->combineDate($checkIn, $request->hotel->check_in_time);
                $checkOut = $this->combineDate($checkOut, $request->hotel->check_out_time);
            }

            // Get stay hours
            $resTime = (int) $request->input('resTime');
            // Use this variable when reservation is direct
            $directResId = 0;

            if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut)) || stayNights($checkIn, $checkOut, true) < 1) {
                return response()->json([
                    'message' => 'Ирэх болон Гарах огноо буруу байгаа тул шалгана уу.',
                ], 400);
            }

            // Find original user
            $user = $request->user();
            // Find hotel
            $hotel = $request->hotel;

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
            $source = $isAutoFill ? Source::where([['hotel_id', $hotel->id], ['is_default', true]])->firstOrFail() : Source::findOrFail($request->input('source.id'));

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

            // Find original partner
            $partner = Partner::find($request->input('partner.id'));

            // Original guest
            $guest = Guest::firstOrCreate(
                [
                    'id' => $request->input('guest.id'),
                    'hotel_id' => $request->hotel->id,
                ],
                [
                    'name' => $request->input('guest.name'),
                    'surname' => $request->input('guest.surname'),
                    'phone_number' => $request->input('guest.phoneNumber'),
                    'email' => $request->input('guest.email'),
                    'passport_number' => $request->input('guest.passportNumber'),
                    'nationality' => $request->input('guest.nationality'),
                    'is_blacklist' => false,
                    'hotel_id' => $hotel->id,
                ]
            );

            $params = $request->only([
                'arrivalTime', 'notes', 'status', 'stayType'
            ]);

            $params = array_merge($params, [
                'hotelId' => $hotel->id,
                'userCloneId' => $userClone->id,
                'sourceCloneId' => $sourceClone->id,
            ]);

            if ($partner) {
                // Create partner clone
                $partnerClone = PartnerClone::create([
                    'name' => $partner->name,
                    'contact_person' => $partner->contact_person,
                    'phone_number' => $partner->phone_number,
                    'description' => $partner->description,
                    'discount' => $partner->discount,
                    'partner_id' => $partner->id,
                ]);

                $params = array_merge($params, [
                    'partnerCloneId' => $partnerClone->id,
                ]);
            }

            // Create group
            $group = Group::firstOrCreate(
                ['id' => $request->input('gid')],
                ['number' => Group::generateUnique(), 'hotel_id' => $hotel->id]
            );

            foreach ($request->input('roomTypes') as $item) {
                if ($isAutoFill) {
                    $item['numberOfGuests'] = 1;
                    $item['numberOfChildren'] = 0;
                    $item['ageOfChildren'] = [];
                    $item['quantity'] = 1;
                }

                // Find original room type
                $roomType = $hotel->roomTypes()
                    ->where('id', $item['id'])
                    ->first();

                if (is_null($roomType)) {
                    return response()->json([
                        'message' => '[' . $item['id'] . ' ID-тай өрөөний төрөл байхгүй байна.',
                    ], 400);
                }

                // If stay night not equal to 'night'
                // Don't create rate plan
                if ($stayType === 'night') {
                    // Find original rate plan
                    $ratePlan = "";
                    if ($isAutoFill) {
                        $ratePlan = $roomType
                            ->ratePlans()
                            ->where('name', 'Үндсэн үнэ')
                            ->first();
                    } else {
                        $ratePlan = $roomType
                            ->ratePlans()
                            ->where('id', $item['ratePlan']['id'])
                            ->first();
                    }

                    if (is_null($ratePlan)) {
                        $ratePlan = $roomType
                            ->ratePlans()
                            ->where('name', 'Үндсэн үнэ')
                            ->first();

                        if (is_null($ratePlan)) {
                            return response()->json([
                                'message' => '[' . $item['id'] . ' ID-тай өрөөний төрлийн ratePlan хоосон байна.',
                            ], 400);
                        }
                    }

                    for ($i = 0; $i < $item['quantity']; $i++) {
                        // Create room type clone
                        $roomTypeClone = RoomTypeClone::create([
                            'sync_id' => $roomType->sync_id,
                            'name' => $roomType->name,
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
                            // if ($ratePlan->is_daily) {
                            $start = Carbon::parse($checkIn)->addDays($j)->format('Y-m-d');
                            $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                            $rate = $ratePlan->getDailyRate($start, $end);

                            if ($rate) {
                                array_push($rates, $rate);
                            } else {
                                array_push($rates, ['id' => null, 'date' => $start, 'value' => $roomType->default_price]);
                            }
                            // }
                        }

                        // if ($ratePlan->is_daily) {
                        //     $rates = $ratePlan->getDailyRates($reqCheckIn, $reqCheckOut);
                        // }
                        // else {
                        //     $rates = $ratePlan->getRates($reqCheckIn, $reqCheckOut);
                        // }

                        // Get numberOfGuests
                        $numberOfGuests = $item['numberOfGuests'];
                        // Get numberOfChildren
                        $numberOfChildren = $item['numberOfChildren'];
                        // Get ageOfChildren
                        $ageOfChildren = $item['ageOfChildren'];

                        // Check age of children define number of guests and children
                        if ($numberOfChildren > 0) {
                            foreach ($ageOfChildren as $age) {
                                if ($age > $hotel->age_child) {
                                    $numberOfGuests += 1;
                                    $numberOfChildren -= 1;
                                }
                            }
                        }

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
                        // $amount = $rates->sum('value') - ($discount * $nights);
                        $amount = array_sum(array_column($rates, 'value')) - ($discount * $nights);

                        $data = array_merge($params, [
                            'amount' => $amount,
                            'number' => $this->model::generateNumber($request),
                            'numberOfGuests' => $numberOfGuests,
                            'numberOfChildren' => $numberOfChildren,
                            'ratePlanCloneId' => $ratePlanClone->id,
                            'roomTypeCloneId' => $roomTypeClone->id,
                            'groupId' => $group->id,
                            'checkIn' => $checkIn,
                            'checkOut' => $checkOut,
                            'exitTime' => $request->input('exitTime'),
                            'statusAt' => Carbon::now(),
                            'postedDate' => $hotel->working_date
                        ]);

                        // Store new reservation
                        $reservation = $this->model::create(snakeCaseKeys($data));

                        // Create children
                        if ($numberOfChildren > 0) {
                            foreach ($ageOfChildren as $age) {
                                if ($age <= $hotel->age_child) {
                                    // Get child policy then access price field
                                    $childPolicy = $hotel->childrenPolicies()
                                        ->where('min', '<=', $age)
                                        ->where('max', '>=', $age)
                                        ->get()->first();

                                    if (is_null($childPolicy)) {
                                        return response()->json([
                                            'message' => 'Алдаа гарлаа. ' . $age . ' настай хүүхдийн үнийн бодлого тохируулаагүй байна.',
                                        ], 400);
                                    }

                                    $childAmount = $childPolicy->price;
                                    if ($childPolicy->price_type === 'percent') {
                                        $childAmount = $amount / 100 * $childPolicy->price;
                                    }
                                    // Create child
                                    $reservation->children()->create([
                                        'age' => $age,
                                        'amount' => $childAmount,
                                        'reservation_id' => $reservation->id,
                                    ]);
                                }
                            }
                        }

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

                        $isDirect = $request->filled('roomId');
                        if ($isDirect) {
                            // Find original room
                            $room = Room::find($request->input('roomId'));

                            if (is_null($room)) {
                                return response()->json([
                                    'message' => '[' . $request->input('roomId') . '] ID-тай өрөөний мэдээлэл байхгүй байна.',
                                ], 400);
                            }

                            $directResId = $this->isDirectReservation($reservation, $room, $roomType, $checkIn);
                        } else {
                            // Auto arrange room
                            $this->autoArrange($reservation);
                        }

                        // Sync taxes to reservation taxes
                        $this->syncTaxes($reservation);
                    }
                } else {
                    for ($i = 0; $i < $item['quantity']; $i++) {
                        // Create room type clone
                        $roomTypeClone = RoomTypeClone::create([
                            'name' => $roomType->name,
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

                        // Get numberOfGuests
                        $numberOfGuests = $item['numberOfGuests'];
                        // Get numberOfChildren
                        $numberOfChildren = $item['numberOfChildren'];
                        // Get ageOfChildren
                        $ageOfChildren = $item['ageOfChildren'];

                        // Check age of children define number of guests and children
                        if ($numberOfChildren > 0) {
                            foreach ($ageOfChildren as $age) {
                                if ($age > $hotel->age_child) {
                                    $numberOfGuests += 1;
                                    $numberOfChildren -= 1;
                                }
                            }
                        }

                        // Total amount of rates
                        $amount = $item['timePrice'];

                        // Check stay type then define res time
                        if ($stayType === 'time') {
                            $checkOut = Carbon::parse($checkIn)->addHour($resTime);
                        }

                        $data = array_merge($params, [
                            'isTime' => true,
                            'amount' => $amount,
                            'number' => $this->model::generateNumber($request),
                            'numberOfGuests' => $numberOfGuests,
                            'numberOfChildren' => $numberOfChildren,
                            'roomTypeCloneId' => $roomTypeClone->id,
                            'groupId' => $group->id,
                            'checkIn' => $checkIn,
                            'checkOut' => $stayType !== 'day' ? $checkOut->format('Y-m-d H:i') : $checkOut,
                            'exitTime' => $stayType !== 'day' ? $checkOut->format('H:i') : $request->input('exitTime'),
                            'statusAt' => Carbon::now(),
                            'postedDate' => $hotel->working_date
                        ]);

                        // Store new reservation
                        $reservation = $this->model::create(snakeCaseKeys($data));

                        // Create children
                        if ($numberOfChildren > 0) {
                            foreach ($ageOfChildren as $age) {
                                if ($age <= $hotel->age_child) {
                                    // Get child policy then access price field
                                    $childPolicy = $hotel->childrenPolicies()
                                        ->where('min', '<=', $age)
                                        ->where('max', '>=', $age)
                                        ->get()->first();

                                    if (is_null($childPolicy)) {
                                        return response()->json([
                                            'message' => 'Алдаа гарлаа. ' . $age . ' настай хүүхдийн үнийн бодлого тохируулаагүй байна.',
                                        ], 400);
                                    }

                                    $childAmount = $childPolicy->price;
                                    if ($childPolicy->price_type === 'percent') {
                                        $childAmount = $amount / 100 * $childPolicy->price;
                                    }
                                    // Create child
                                    $reservation->children()->create([
                                        'age' => $age,
                                        'amount' => $childAmount,
                                        'reservation_id' => $reservation->id,
                                    ]);
                                }
                            }
                        }

                        $value = $amount + $reservation->childrenAmount();

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

                        // Check is direct reservation
                        $isDirect = $request->filled('roomId');
                        if ($isDirect) {
                            // Find original room
                            $room = Room::findOrFail($request->input('roomId'));

                            if (is_null($room)) {
                                return response()->json([
                                    'message' => '[' . $request->input('roomId') . '] ID-тай өрөөний мэдээлэл байхгүй байна.',
                                ], 400);
                            }

                            $directResId = $this->isDirectReservation($reservation, $room, $roomType, $checkIn);
                        } else {
                            // Auto arrange room
                            $this->autoArrange($reservation);
                        }

                        // Update reservation amount
                        $reservation->update([
                            'amount' => $reservation->calculate(),
                        ]);

                        // Sync taxes to reservation taxes
                        $this->syncTaxes($reservation);
                    }
                }
            }

            // Commit transaction
            DB::commit();

            // Check is sync
            if ($hotel->has_ihotel && !is_null($hotel->sync_id)) {
                // Sync hotel to services
                event(new ReservationCreated($group, new User, false, 'myhotel'));
            }

            // Check send email
            if ($isSendEmail) {
                // Send email to guest
                event(new ReservationEmailSend($group->id, $guest->email, 'reservation', true));
            }

            return response()->json([
                'group' => $group,
                'isDirect' => $isDirect,
                'resId' => $directResId,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Return available rooms by reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableRooms($id)
    {
        // Current reservation
        $model = $this->newQuery()
            ->where('reservations.id', $id)
            ->firstOrFail();

        return response()->json([
            'rooms' => $model->availableRooms(),
        ]);
    }

    /**
     * Assign room.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRoom(Request $request)
    {
        // MySQL transaction
        DB::beginTransaction();

        try {
            $model = $this->newQuery()
                ->where('reservations.id', $request->input('id'))
                ->firstOrFail();

            // Delete old one
            $model->roomClone->delete();

            // Test return
            // return response()->json([
            //     'room.id' => $request->filled('room.id'),
            // ]);

            if ($request->filled('room.id')) {
                // Find original room
                $room = $model->availableRooms()
                    ->firstWhere('id', $request->input('room.id'));

                // Create room clone
                $roomClone = RoomClone::create([
                    'name' => $room->name,
                    'ic_name' => $room->ic_name,
                    'status' => $room->status,
                    'description' => $room->description,
                    'room_id' => $room->id,
                ]);

                $model->roomClone()->associate($roomClone);
                $model->save();
            } else {
                $model->roomClone()->dissociate();
                $model->save();

                // Refresh to load default relation
                $model->refresh();
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'roomClone' => $model->roomClone,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /** FIX THIS
     * Get all resources by date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDates(Request $request)
    {
        $isTime = $request->input('isTime', false);

        $request->validate([
            'startDate' => 'required|date_format:Y-m-d' . ($isTime ? ' H:i' : ''),
            'endDate' => 'nullable|date_format:Y-m-d' . ($isTime ? ' H:i' : ''),
            'status' => 'nullable|string',
            'search' => 'nullable|string'
        ]);

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = $this->newQuery();

        // If endDate is null return current date unassigned reservations
        if ($endDate !== null) {
            $query->where([
                ['check_in', '<=', $endDate],
                ['check_out', '>=', $startDate]
            ]);
        } else {
            $query->whereDate('check_in', '=', $startDate);
        }

        $query->where('status', '<>', 'canceled')
            ->when($status, function ($query) use ($status) {
                $query->whereStatus($status);
            })->when($search, function ($query, $search) {
                return $query->search($search);
            });

        $assignee = $request->query('assignee');

        $assignee === 'assigned'
            ? $query->has('roomClone.room')
            : $query->doesntHave('roomClone.room');

        $reservations = $query->get()
            ->each(function ($items) {
                $items->append('isGroup');
            });

        // Load relations
        if ($request->has('with')) {
            $reservations->load($request->query('with'));
        }

        return response()->json([
            'reservations' => $reservations,
        ]);
    }

    /**
     * Check reservation update availability.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'checkIn' => 'required|date_format:Y-m-d H:i',
            'checkOut' => 'required|date_format:Y-m-d H:i',
            'roomId' => 'nullable|integer',
            'resTime' => 'nullable|integer',
            'timePrice' => 'nullable|integer',
        ]);

        $id = $request->input('id');
        $checkIn = $request->input('checkIn');
        $checkOut = $request->input('checkOut');
        $roomId = $request->input('roomId');

        if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut))) {
            return response()->json([
                'message' => 'Гарах огноог ирэх огноогоос өмнө байхаар оруулж болохгүй тул тул шалгана уу.',
            ], 400);
        }

        $reservation = $this->newQuery()
            ->where('id', $id)
            ->with(['roomClone', 'ratePlanClone'])
            ->firstOrFail();

        if ($reservation->amount_paid !== 0) {
            return response()->json([
                'available' => false,
                'message' => 'Захиалгын төлбөр төлөлт үүссэн байна.',
            ]);
        }

        if ($reservation->stay_type === 'time' && $request->filled('resTime')) {
            // Get stay hours
            $resTime = (int) $request->input('resTime');
            $checkOut = Carbon::parse($checkIn)
                ->addHour($resTime);
        }

        // Check if reservation is checked-out
        if ($reservation->status === 'checked-out') {
            return response()->json([
                'available' => false,
                'message' => 'Захиалгын мэдээлэл өөрчлөх боломжгүй байна.',
            ]);
        }

        // Check if reservation is checked-in OR no-show and not change checkIn date
        if (($reservation->status === 'no-show' || $reservation->status === 'checked-in') && !Carbon::parse($reservation->check_in)->eq(Carbon::parse($checkIn))) {
            return response()->json([
                'available' => false,
                'message' => ($reservation->status === 'no-show' ? 'Ирээгүй төлөвтэй' : 'Байрлаж байгаа') . ' захиалгын эхлэх огноог солих боломжгүй байна.',
            ]);
        }

        // Find requested room
        $room = Room::find($roomId);

        // Check reservation is assigned to room
        if (is_null($room)) {
            return response()->json([
                'available' => false,
                'message' => 'Тус захиалгыг өрөөнд хувиарлаагүй байна.',
            ]);
        }

        // Get default price
        $defaultPrice = $room->roomType->default_price;

        // Check room type
        if ($reservation->roomTypeClone->room_type_id !== $room->room_type_id) {
            return response()->json([
                'available' => false,
                'message' => 'Тус өрөөнд захиалгыг шилжүүлэх боломжгүй байна.',
            ]);
        }

        // Check requested room is available
        $room = Room::where('id', $roomId)
            ->unassigned($checkIn, $checkOut, $reservation->id)
            ->first();

        // if (!$ratePlan || !$room) {
        if (is_null($room)) {
            return response()->json([
                'available' => false,
                'message' => 'Захиалгын огноог өөрчлөх боломжгүй байна. Өрөө захиалгатай байна.',
            ]);
        }

        // Check is time reservation
        if ($reservation->is_time) {
            // Get stay minutes
            $nights = stayMinutes($checkIn, $checkOut);
            $maxTime = $reservation->hotel->max_time;

            // Check max time
            if ($nights > ($maxTime * 60)) {
                return response()->json([
                    'available' => false,
                    'message' => 'Цагийн захиалга хүлээн авах дээд цаг ' . $maxTime . ' байгаа тул захиалгын байрлах хугацааг сунгах боломжгүй байна.',
                ]);
            }
            // If time price changed
            if ($request->filled('timePrice')) {
                $timePrice = $request->input('timePrice');
                $total = $reservation->calculate(null, false, false, $timePrice);
            } else {
                $total = $reservation->calculate(null, false, false);
            }
        } else {
            // Get stay nights
            $nights = stayNights($checkIn, $checkOut, false);

            // Find original ratePlan
            $ratePlan = null;
            if ($reservation->rate_plan_clone_id) {
                $ratePlan = $reservation->ratePlanClone
                    ->ratePlan()
                    // ->availableIn($checkIn, $checkOut)
                    ->first();
            }

            // Calculate new price of reservation
            $rates = [];

            // Get push rates
            for ($i = 0; $i < $nights; $i++) {
                $start = Carbon::parse($checkIn)->addDays($i)->format('Y-m-d');
                $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                // Check has rate plan
                if ($ratePlan) {
                    $rate = $ratePlan->getDailyRate($start, $end);

                    if ($rate) {
                        array_push($rates, $rate);
                    } else {
                        array_push($rates, ['id' => null, 'date' => $start, 'value' => $defaultPrice]);
                    }
                } else {
                    array_push($rates, ['id' => null, 'date' => $start, 'value' => $defaultPrice]);
                }
            }

            // $rates = $ratePlan->getDailyRates($checkIn, $checkOut);
            $total = $reservation->calculate($rates, true, false);
        }

        return response()->json([
            'total' => (int) ceil($total),
            'balance' => $reservation->amount - $total,
            'available' => true,
            'checkOut' => Carbon::parse($checkOut)->format('Y-m-d H:i'),
        ]);
    }

    /**
     * Store the resources in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDates(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'checkIn' => 'required|date_format:Y-m-d H:i',
            'checkOut' => 'required|date_format:Y-m-d H:i',
            'roomId' => 'required|integer',
            'timePrice' => 'nullable|integer',
            'resTime' => 'nullable|integer',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Reservation id set
            $id = $request->input('id');
            // Find hotel
            $hotel = $request->hotel;
            // Reservation first
            $reservation = $this->newQuery()
                ->where('id', $id)
                ->with(['roomClone', 'ratePlanClone'])
                ->firstOrFail();

            if ($reservation->amount_paid !== 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Захиалгын төлбөр төлөлт үүссэн байна.',
                ]);
            }

            $checkIn = $request->input('checkIn');
            $checkOut = $request->input('checkOut');

            // Room id
            $roomId = $request->input('roomId');

            // Find requested room
            $room = Room::where('id', $roomId)
                ->unassigned($checkIn, $checkOut, $reservation->id)
                ->first();

            if (!$room) {
                return response()->json([
                    'message' => 'Алдаа гарлаа. Өрөөний бүртгэл олдсонгүй.',
                ], 400);
            }

            // Get default price of room
            $defaultPrice = $room->roomType->default_price;
            // Get stay nights or times
            $nights = stayNights($checkIn, $checkOut, $reservation->is_time);

            // Delete current rates
            $reservation->dayRates()->delete();

            if ($reservation->is_time) {
                if ($request->filled('timePrice')) {
                    // $maxTime = $reservation->hotel->max_time;
                    $value = $request->input('timePrice') + $reservation->childrenAmount();

                    // Create day rates
                    DayRate::create([
                        'date' => Carbon::parse($checkIn)->format('Y-m-d'),
                        'value' => $value,
                        'default_value' => $value,
                        'reservation_id' => $reservation->id,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Цагийн захиалгын үнийг оруулна уу.',
                    ], 400);
                }
            } else {
                // Find original ratePlan
                $ratePlan = null;
                if ($reservation->rate_plan_clone_id) {
                    $ratePlan = $reservation->ratePlanClone
                        ->ratePlan()
                        // ->availableIn($checkIn, $checkOut)
                        ->first();
                }

                // Calc rate then create day rates of reservation
                for ($i = 0; $i < $nights; $i++) {
                    $start = Carbon::parse($checkIn)->addDays($i)->format('Y-m-d');
                    $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                    // Check has rate plan
                    if ($ratePlan) {
                        $rate = $ratePlan->getDailyRate($start, $end);

                        if ($rate) {
                            $value = $rate->value + $reservation->childrenAmount();
                            DayRate::create([
                                'date' => $rate->date,
                                'value' => $value,
                                'default_value' => $value,
                                'reservation_id' => $reservation->id,
                            ]);
                        } else {
                            $value = $defaultPrice + $reservation->childrenAmount();
                            DayRate::create([
                                'date' => $start,
                                'value' => $value,
                                'default_value' => $value,
                                'reservation_id' => $reservation->id,
                            ]);
                        }
                    } else {
                        $value = $defaultPrice + $reservation->childrenAmount();
                        DayRate::create([
                            'date' => $start,
                            'value' => $value,
                            'default_value' => $value,
                            'reservation_id' => $reservation->id,
                        ]);
                    }
                }
            }

            // Update payment history /payments/
            // foreach ($reservation->payments as $payment) {
            //     foreach ($payment->items as $item) {
            //         if ($item->item_type === 'room') {
            //             $item->price = $rates->sum('value');
            //             $item->update();

            //             $payment->update([
            //                 'amount_total' => $payment->calculate(),
            //             ]);
            //         }
            //     }
            // }

            $reservation->roomClone()->update([
                'name' => $room->name,
                'room_id' => $roomId,
            ]);

            $reservation->update([
                'amount' => $reservation->calculate(),
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'arrival_time' => Carbon::parse($checkIn)->format('H:i'),
                'exit_time' => Carbon::parse($checkOut)->format('H:i'),
            ]);

            // Commit transaction
            DB::commit();

            // Check is sync
            if ($hotel->has_ihotel && !is_null($hotel->sync_id)) {
                // Sync reservation
                event(new ReservationUpdated([$reservation]));
            }

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update multiple reservations status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateMultiple(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'updateMultipleRules');

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Find hotel
            $hotel = $request->hotel;
            // Selected reservations ids
            $resIds = $request->input('reservations');
            $status = $request->input('status');

            $reservations = $this->model::whereIn('id', $resIds)->get();
            if ($request->filled('status')) {
                foreach ($reservations as $reservation) {
                    // Get old status and id
                    $oldStatus = $reservation->status;
                    $resId = $reservation->id;
                    $checkIn = $reservation->check_in;
                    $checkOut = $reservation->check_out;

                    // If guest check in date is not today
                    if ($status === 'checked-in') {
                        $roomId = $reservation->roomClone->room_id;

                        if ($checkIn > Carbon::now()->format('Y-m-d H:i')) {
                            return response()->json([
                                'message' => $reservation->number . ' захиалгын зочны ирэх хугацаа болоогүй байна.',
                            ], 400);
                        }

                        $isRoom = $this->newQuery()
                            ->where('status', 'checked-in')
                            ->where('check_out', $checkIn)
                            ->whereHas('roomClone', function ($query) use ($roomId) {
                                $query->where('room_id', $roomId);
                            })
                            ->count();

                        if ($isRoom >= 1) {
                            return response()->json([
                                'message' => $reservation->number . 'захиалгын өмнөх зочин байрлаж байна.',
                            ], 400);
                        }
                    }

                    // if ($oldStatus === 'checked-in' && $status === 'no-show') {
                    //     return response()->json([
                    //         'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
                    //     ], 400);
                    // }

                    // If guest checked out then change room status to dirty
                    if ($status === 'checked-out') {
                        $roomId = $reservation->roomClone->room_id;
                        $room = Room::find($roomId);
                        $room->status = 'dirty';
                        $room->save();
                    }

                    // If reservation status is canceled
                    if ($oldStatus === 'canceled' && $status !== 'canceled') {
                        // Is room free
                        $isRoomFree = false;

                        if ($reservation->roomClone->default) {
                            // Rooms unassigned
                            $isRoomFree =  $reservation->roomTypeClone->roomType->rooms()->unassigned($checkIn, $checkOut, $reservation->id)->exists();
                        } else {
                            // Room unassigned
                            $isRoomFree = $reservation->roomClone
                                ->room()
                                ->unassigned($checkIn, $checkOut, $reservation->id)
                                ->exists();
                        }

                        // If rooms true
                        if (!$isRoomFree) {
                            return response()->json([
                                'message' => $reservation->number . ' захиалгыг буцаах боломжгүй байна.',
                            ], 400);
                        }
                    }

                    // Fix here
                    if ($status === 'canceled') {
                        // If cancel reservation then is active = false payments
                        $reservation->payments()->where('is_active', 1)->update(['is_active' => 0]);

                        // Find original user
                        $user = $request->user();

                        $hotelId = $request->hotel->id;

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
                                    'amount' => 0,
                                    'is_paid' => false,
                                ]);
                            } else {
                                // Create cancellation
                                Cancellation::create([
                                    'hotel_id' => $hotelId,
                                    'user_clone_id' => $userClone->id,
                                    'reservation_id' => $reservation->id,
                                    'amount' => 0,
                                    'is_paid' => false,
                                ]);
                            }
                        }
                    } else {
                        if ($reservation->cancellation && !$reservation->is_time) {
                            $reservation->cancellation->delete();
                        }

                        // If return cancel reservation then is active = false payments
                        $reservation->payments()->where('is_active', 0)->update(['is_active' => 1]);
                    }

                    // Update status changed date
                    if ($status !== $oldStatus) {
                        $reservation->status_at = Carbon::now();
                        $reservation->save();
                    }

                    $reservation->status = $status;
                    $reservation->update();
                }
            } else {
                return response()->json([
                    'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
                ], 400);
            }

            // Commit transaction
            DB::commit();

            // Check hotel for sync
            // if ($hotel->has_ihotel && !is_null($hotel->sync_id)) {
            //     // Sync hotel to services
            //     event(new ReservationUpdated($reservations->toArray()));
            // }

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update discount.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDiscount(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'discountType' => 'required|string',
            'discount' => [
                'required',
                'integer',
                $request->input('discountType') == 'percent' ? 'max:100' : ''
            ],
        ]);

        $id = $request->input('id');
        $discountType = $request->input('discountType');
        $discount = $request->input('discount');

        // MySQL transaction
        DB::beginTransaction();

        try {
            $reservation = $this->newQuery()
                ->where('id', $id)
                ->firstOrFail();

            if ($reservation->amount_paid !== 0) {
                return response()->json([
                    'message' => 'Захиалгын төлбөр төлөлт үүссэн байна.',
                ], 400);
            }

            $reservation->update([
                'discount_type' => $discountType,
                'discount' => $discount,
            ]);

            if ($discountType === 'currency') {
                // Calc each day rate discount
                $discount = $discount / $reservation->stay_nights;
            }

            // Update dayrates by discount
            foreach ($reservation->dayRates as $dayRate) {
                $value = $dayRate->default_value;

                if ($discountType === 'percent') {
                    $dayRate->value = $value - calculatePercent($value, $discount);
                } else {
                    $dayRate->value = ($value - $discount);
                }
                $dayRate->update();
            }

            // Update amount
            $reservation->update([
                'amount' => $reservation->calculate(),
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update cancellation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCancellation(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'amount' => 'required|integer',
            'isPaid' => 'required|boolean',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            $reservation = $this->newQuery()
                ->where('id', $request->input('id'))
                ->firstOrFail();

            $reservation->cancellation->update([
                // 'amount' => $request->input('amount'),
                'is_paid' => $request->input('isPaid'),
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Return reservations report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportReservations(Request $request)
    {
        // Validation
        $request->validate([
            'type' => 'required|string|in:todayCheckin,todayCheckout,todayStaying,tomorrowCheckin,cancellations',
        ]);

        $type = $request->query('type');
        $workingDate = $request->hotel->working_date;
        $tomorrow = Carbon::parse($workingDate)->addDay(1);

        // Get reservations
        $reservations = $this->newQuery()
            ->select(['id', 'is_time', 'number', 'check_in', 'check_out', 'group_id', 'status', 'amount', 'room_type_clone_id', 'room_clone_id'])
            ->with(['guestClone', 'roomTypeClone', 'roomClone', 'roomClone.room', 'cancellationPolicyClone', 'cancellation'])
            ->when($type === 'todayCheckin', function ($query) use ($workingDate) {
                $query->whereDate('check_in', $workingDate)
                    ->whereIn('status', ['pending', 'confirmed', 'no-show']);
            })
            ->when($type === 'todayCheckout', function ($query) use ($workingDate) {
                $query->whereDate('check_out', $workingDate)
                    ->where('status', 'checked-in');
            })
            ->when($type === 'tomorrowCheckin', function ($query) use ($tomorrow) {
                $query->whereDate('check_in', $tomorrow)
                    ->whereIn('status', ['pending', 'confirmed', 'no-show']);
            })
            ->when($type === 'todayStaying', function ($query) use ($workingDate) {
                $query->whereDate('check_in', '<=', $workingDate)
                    ->whereDate('check_out', '>=', $workingDate)
                    ->where('status', 'checked-in');
            })
            ->when($type === 'cancellations', function ($query) use ($workingDate) {
                $query->whereDate('status_at', $workingDate)
                    ->where('status', 'canceled');
            })
            ->get();

        return response()->json($reservations);
    }

    /**
     * Get items by reservations ids.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getItems(Request $request)
    {
        // Validation
        $request->validate([
            'ids' => 'required|array',
        ]);

        try {
            $items = Item::whereIn('reservation_id', $request->input('ids'))
                ->join('service_category_clones AS scc', 'items.service_category_clone_id', '=', 'scc.id')
                ->join('service_clones AS sc', 'items.service_clone_id', '=', 'sc.id')
                ->select(DB::raw('sum(items.quantity) as totalQuantity'), 'sc.name', 'scc.name AS categoryName', 'items.price')
                ->groupBy('sc.service_id', 'scc.service_category_id')
                ->get();

            return response()->json([
                'data' => $items
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Something went wrong.'
            ], 400);
        }
    }

    /**
     * Get reservation data
     */
    public function getReservation(Request $request, $id)
    {
        // withoutExtraData 
        $withoutExtraData = $request->input('withoutExtraData', false);
        // Get hotel
        $hotel = $request->hotel;

        // Find reservation
        $reservation = $this->model::with($request->query('with'))->find($id);

        if (is_null($reservation)) {
            return response()->json([
                'message' => 'Record not found.'
            ], 404);
        }

        $currencies = $hotel
            ->currencies()
            ->select(['id', 'name', 'short_name', 'rate', 'is_default'])
            ->get();

        $extraBedPolicies = $hotel
            ->extraBedPolicies()
            ->select(['id', 'min', 'max', 'age_type', 'price_type', 'price'])
            ->get();

        $paymentMethods = $hotel
            ->paymentMethods()
            ->select(['id', 'name', 'is_default', 'is_paid', 'income_types'])
            ->get();

        $serviceCategories = $hotel
            ->serviceCategories()
            ->with('services')
            ->select(['id', 'name'])
            ->get();

        $taxes = $hotel
            ->taxes()
            ->select(['id', 'name', 'is_default', 'percentage', 'inclusive'])
            ->get();
        if ($request->query("withoutExtraData") == "false") {
            $data = [
                'hotel' => [
                    'id' => $hotel->id,
                    'maxTime' => $hotel->max_time,
                    'isMustPay' => $hotel->hotelSetting->is_must_pay
                ],
                'reservation' => $reservation,
                'currencies' => $currencies,
                'extraBedPolicies' => $extraBedPolicies,
                'paymentMethods' => $paymentMethods,
                'serviceCategories' => $serviceCategories,
                'taxes' => $taxes
            ];
        } else {
            $data = [
                'reservation' => $reservation
            ];
        }
        return response()->json($data);
    }


    /**
     * Get service data
     */
    public function getService(Request $request, $id)
    {
        // Get hotel
        $hotel = $request->hotel;

        // Find reservation
        $reservation = $this->model::find($id);

        if (is_null($reservation)) {
            return response()->json([
                'message' => 'Record not found.'
            ], 404);
        }

        $currencies = $hotel
            ->currencies()
            ->select(['id', 'name', 'short_name', 'rate', 'is_default'])
            ->get();

        $extraBedPolicies = $hotel
            ->extraBedPolicies()
            ->select(['id', 'min', 'max', 'age_type', 'price_type', 'price'])
            ->get();

        $paymentMethods = $hotel
            ->paymentMethods()
            ->select(['id', 'name', 'is_default', 'is_paid', 'income_types'])
            ->get();

        $serviceCategories = $hotel
            ->serviceCategories()
            ->with('services')
            ->select(['id', 'name'])
            ->get();

        $taxes = $hotel
            ->taxes()
            ->select(['id', 'name', 'is_default', 'percentage', 'inclusive'])
            ->get();

        $data = [
            'hotel' => [
                'id' => $hotel->id,
                'maxTime' => $hotel->max_time,
                'isMustPay' => $hotel->hotelSetting->is_must_pay
            ],
            'reservation' => $reservation,
            'currencies' => $currencies,
            'extraBedPolicies' => $extraBedPolicies,
            'paymentMethods' => $paymentMethods,
            'serviceCategories' => $serviceCategories,
            'taxes' => $taxes
        ];

        return response()->json($data);
    }

    /**
     * Get hotel new reservation data
     */
    public function getNewResData(Request $request)
    {
        $hotel = $request->hotel;
        $sources = $hotel
            ->sources()
            ->select(['id', 'name', 'is_default', 'service_name'])
            ->get();

        // $partners = $hotel
        //     ->partners()
        //     ->select(['id', 'name'])
        //     ->get();

        // $childrenPolicies = $hotel
        //     ->childrenPolicies()
        //     ->select(['id', 'min', 'max'])
        //     ->get();

        $data = [
            'hotel' => [
                'checkInTime' => $hotel->check_in_time,
                'checkOutTime' => $hotel->check_out_time,
                'hasTime' => $hotel->has_time,
                'maxTime' => $hotel->max_time,
                'ageChild' => $hotel->age_child,
            ],
            'sources' => $sources,
            // 'partners' => $partners,
            // 'childrenPolicies' => $childrenPolicies
        ];

        return response()->json($data);
    }

    /**
     * Check reservations for night audit
     */
    public function checkNightAudit(Request $request)
    {
        $hotel = $request->hotel;

        $checkInReservations = $hotel->reservations()
            // ->where('is_audited', 0)
            ->whereIn('status', ['pending', 'confirmed', 'no-show'])
            // ->where('check_in', '<', $hotel->working_date)
            ->where('check_in', '>=', $hotel->working_date)
            ->with(['roomTypeClone:id,name', 'roomClone:id,name', 'guestClone'])
            ->select(['id', 'number', 'check_in', 'check_out', 'status', 'amount', 'room_type_clone_id', 'room_clone_id'])
            ->orderBy('created_at', 'DESC')
            ->get();

        $checkOutReservations = $hotel->reservations()
            // ->where('check_out', '<', $hotel->working_date)
            // ->where('is_audited', 0)
            ->where('status', 'checked_in')
            ->where('check_out', '>=', $hotel->working_date)
            ->where('check_out', '<', Carbon::parse($hotel->working_date)->addDay(1))
            ->with(['roomTypeClone:id,name', 'roomClone:id,name', 'guestClone'])
            ->select(['id', 'number', 'check_in', 'check_out', 'status', 'amount', 'room_type_clone_id', 'room_clone_id'])
            ->orderBy('created_at', 'DESC')
            ->get();

        $data = [
            'hotel' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'has_night_audit' => $hotel->hotelSetting->has_night_audit,
                'is_nightaudit_auto' => $hotel->hotelSetting->is_nightaudit_auto,
                'working_date' => $hotel->working_date
            ],
            'checkInReservations' => $checkInReservations,
            'checkOutReservations' => $checkOutReservations
        ];

        return response()->json($data);
    }

    /**
     * Get night audit data
     */
    public function getNightAuditReport(Request $request)
    {
        $hotel = $request->hotel;
        $user = $request->user();
        $filterDate = $request->filled('filterDate') ? $request->filterDate : $hotel->working_date;

        $totalSellRoomsCount = count($hotel->rooms);
        // $hotel->rooms()
        // ->unassigned($filterDate, $filterDate)
        // ->count();

        /* Calculate occupied rooms */
        $occupiedReservations = $hotel->reservations()
            ->whereDate('check_in', '<=', $filterDate)
            ->whereDate('check_out', '>=', $filterDate)
            ->whereNotNull('room_clone_id')
            ->where('status', 'checked-in')
            ->get();
        $totalOccupied = $occupiedReservations->count();
        $totalOccupiedChildren = $occupiedReservations->sum('number_of_children');

        /* Calculate available rooms */
        $totalAvailable = $totalSellRoomsCount - $totalOccupied;

        /* Calculate check-in rooms */
        $checkInReservations = $hotel->reservations()
            ->whereDate('check_in', $filterDate)
            ->whereIn('status', [
                'pending', 'confirmed'
            ])
            ->get();
        $totalCheckIn = $checkInReservations->count();
        $totalCheckInChildren = $checkInReservations->sum('number_of_children');

        /* Calculate check-out rooms */
        $checkOutReservations = $hotel->reservations()
            ->whereDate('check_in', $filterDate)
            ->whereIn('status', [
                'pending', 'confirmed',
            ])
            ->get();
        $totalCheckOut = $checkOutReservations->count();
        $totalCheckOutChildren = $checkOutReservations->sum('number_of_children');

        $data = [
            'hotel' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'has_night_audit' => $hotel->hotelSetting->has_night_audit,
                'is_nightaudit_auto' => $hotel->hotelSetting->is_nightaudit_auto,
                'working_date' => $hotel->working_date
            ],
            'user' => [
                'name' => $user->name,
            ],
            'roomDetails' => [
                'occupiedRooms' => [
                    'percent' => number_format(($totalOccupied / $totalSellRoomsCount) * 100, 2),
                    'total' => $totalOccupied,
                    'totalGuests' => $occupiedReservations->sum('number_of_guests') . '' . ($totalOccupiedChildren > 0 ? ' + ' . $totalOccupiedChildren : ''),
                    'items' => []
                ],
                'availableRooms' => [
                    'percent' => number_format(($totalAvailable / $totalSellRoomsCount) * 100, 2),
                    'total' => $totalAvailable,
                    'totalGuests' => '-',
                    'items' => []
                ],
                'checkIns' => [
                    'percent' => number_format(($totalCheckIn / $totalSellRoomsCount) * 100, 2),
                    'total' => $totalCheckIn,
                    'totalGuests' => $checkInReservations->sum('number_of_guests') . '' . ($totalCheckInChildren > 0 ? ' + ' . $totalCheckInChildren : ''),
                    'items' => []
                ]
            ]
            // 'checkOutReservations' => $checkOutReservations,
            // 'checkInReservations' => $checkInReservations
        ];

        return response()->json($data);
    }

    /**
     * Perform reservations for night audit
     */
    public function performNightAudit(Request $request)
    {
        $hotel = $request->hotel;

        if ($hotel->hotelSetting->has_night_audit) {
            $currentDate = Carbon::now();
            $tomorrow = Carbon::parse($hotel->working_date)->addDay(1);

            // Check is already performed
            // if (Carbon::parse($hotel->working_date)->lt($currentDate)) {
            // Check time to perform na
            if ($currentDate->gte($tomorrow)) {
                $hotel->working_date = $tomorrow->toDateString();
                $hotel->update();
            } else {
                return response()->json([
                    'message' => trans('messages.na_not_time')
                ], 400);
            }
            // } else {
            //     return response()->json([
            //         'message' => trans('messages.na_already_done', ['date' => $hotel->working_date])
            //     ], 400);
            // }
        } else {
            return response()->json([
                'message' => 'Something went wrong. Try again later.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully performed night audit.',
            'data' => $hotel->working_date
        ]);
    }

    /**
     * Perform isDirect reservation
     */
    public function isDirectReservation($reservation, $room, $roomType, $checkIn)
    {
        $roomClone = RoomClone::create([
            'name' => $room->name,
            'ic_name' => $room->ic_name,
            'status' => $room->status,
            'room_id' => $room->id,
            'description' => $room->description
        ]);

        $checkReservation = $this->model::whereDate('check_out', $checkIn)
            ->where('status', 'checked-in')
            ->whereHas('roomTypeClone', function ($query) use ($roomType) {
                $query->where('room_type_id', $roomType->id);
            })
            ->whereHas('roomClone', function ($query) use ($room) {
                $query->where('room_id', $room->id);
            })
            ->first();

        if (!is_null($checkReservation)) {
            $reservation->status = 'pending';
        }

        $reservation->roomClone()->associate($roomClone);
        $reservation->save();

        return $reservation->id;
    }

    /**
     * Perform Auto Arrange Room for Reservation
     */
    public function autoArrange($reservation)
    {
        $hotel = request()->hotel;
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
    }

    /**
     * Perform Sync Taxes
     */
    public function syncTaxes($reservation)
    {
        $hotel = request()->hotel;
        // Sync taxes to reservation taxes
        foreach ($hotel->taxes as $tax) {
            if ($tax->is_enabled) {
                // Create tax clones for reservation
                $data = TaxClone::create([
                    'name' => $tax->name,
                    'percentage' => $tax->percentage,
                    'inclusive' => $tax->inclusive,
                    'key' => $tax->key,
                    'is_default' => $tax->is_default,
                    'is_enabled' => $tax->is_enabled,
                    'reservation_id' => $reservation->id,
                    'tax_id' => $tax->id,
                ]);

                if (!$data->inclusive) {
                    // Update reservation amount
                    $reservation = $data->reservation;
                    $reservation->update([
                        'amount' => $reservation->calculate(),
                    ]);
                }
            }
        }
    }

    public function combineDate($date, $time)
    {
        return Carbon::parse($date)->year . "-" . Carbon::parse($date)->month . "-" .
            Carbon::parse($date)->day . " " . $time;
    }
}
