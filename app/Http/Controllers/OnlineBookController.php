<?php

namespace App\Http\Controllers;

use App\Models\{
    Reservation, Hotel, FacilityCategory,
    CancellationPolicyClone, DayRate, Guest, GuestClone,
    Group, RoomClone, RoomTypeClone, RatePlanClone,
    SourceClone, TaxClone, User, UserClone 
};
use App\Events\{ReservationCreated, ReservationEmailSend};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\{DB};

class OnlineBookController extends Controller
{
    /**
     * Fetch hotel on online booking page from facebook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fetchHotel(Request $request)
    {
        // Validation
        $request->validate([
            'slug' => 'required|string',
        ]);

        // Default checkIn | checkOut
        $checkIn = Carbon::today()->format('Y-m-d');
        $checkOut = Carbon::tomorrow()->format('Y-m-d');

        // Find hotel by slug
        $hotel = Hotel::whereSlug($request->input('slug'))
            // ->withAndwhereHas('roomTypes', function ($query) use ($checkIn, $checkOut) {
            //     $query
            //         ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
            //             $query->unassigned($checkIn, $checkOut);
            //         }])
            //         ->where('default_price', '>', 0);
            // })
            ->first();
        $roomTypes = $hotel->roomTypes()
            ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                $query->unassigned($checkIn, $checkOut);
            }])
            ->with('bedType')
            ->where('default_price', '>', 0)
            ->get();

        // Calculate enabled and inclusive tax percent
        $taxPercent = $hotel->taxes()
            ->where('is_enabled', true)
            ->where('inclusive', false)
            ->sum('percentage');

        // Get stay nights
        $nights = stayNights($checkIn, $checkOut, false);

        // Calculate roomTypes price 
        foreach ($roomTypes as $item) {
            $ratePlan = $item
                ->ratePlans()
                ->where('is_online_book', true)
                ->first();

            // Calculate new price of reservation
            $rates = [];

            // If has ratePlan
            if ($ratePlan) {
                // Calculate rates
                for ($i = 0; $i < $nights; $i++) {
                    if ($ratePlan->is_daily) {
                        $start = Carbon::parse($checkIn)->addDays($i)->format('Y-m-d');
                        $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                        $rate = $ratePlan->getDailyRate($start, $end);

                        if ($rate) {
                            array_push($rates, $rate->value + calculatePercent($rate->value, $taxPercent));
                        } else {
                            array_push($rates, $item->default_price + calculatePercent($item->default_price, $taxPercent));
                        }
                    }
                }
                $price = array_sum($rates);
            } else {
                $price = $nights * ($item->default_price + calculatePercent($item->default_price, $taxPercent));
            }

            $item->online_price = $price;
        }

        if ($hotel) {
            if ($request->filled('with')) {
                $hotel->load($request->input('with'));
            }
            // $hotel->load(['hotelBanks']);
            // Get hotel facilities
            $facilityIds = $hotel->facilities()->pluck('facilities.id');
            // Get grouped facility categories
            $facilityCategories = FacilityCategory::
                withAndwhereHas('facilities', function ($query) use ($facilityIds) {
                    $query->whereIn('id', $facilityIds);
                })
                ->get();

            return response()->json([
                'status' => true,
                'message' => '',
                'hotel' => $hotel,
                'roomTypes' => $roomTypes,
                'minDate' => $checkIn,
                'minCheckoutDate' => $checkOut,
                'facilityCategories' => $facilityCategories,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found.',
            ], 404);
        }
    }

    /**
     * Сул өрөөтэй өрөөний төрлүүд хайх.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fetchRoomTypes(Request $request)
    {
        $isTime = $request->input('isTime', false);

        // Validation
        $request->validate([
            'slug' => 'required|string',
            'checkIn' => 'required|date_format:Y-m-d H:i',
            'checkOut' => 'required|date_format:Y-m-d H:i',
            'resTime' => ($isTime ? 'required' : 'nullable') . '|integer',
            'resType' => 'nullable|in:night,day,time',
        ]);

        $hotel = Hotel::whereSlug($request->input('slug'))->first();

        $checkIn = $request->input('checkIn');
        $checkOut = $request->input('checkOut');
        $resType = $request->input('resType');
        $resTime = (int) $request->input('resTime');

        if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut)) || stayNights($checkIn, $checkOut, true) < 1) {
            return response()->json([
                'message' => 'Ирэх болон Гарах огноо буруу байгаа тул шалгана уу.',
            ], 400);
        }

        if ($isTime && $resType === 'time') {
            $checkOut = Carbon::parse($checkIn)
                ->addHour($resTime)
                ->format('Y-m-d H:i');
        }

        $roomId = $request->input('roomId');
        $roomTypeId = $request->input('roomTypeId');

        // Check room types
        $roomTypes = $hotel->roomTypes()
            ->with(['rooms' => function ($query) use ($checkIn, $checkOut, $roomId) {
                $query->select(['id', 'name', 'status', 'room_type_id'])
                    ->unassigned($checkIn, $checkOut)
                    ->when($roomId, function($query) use ($roomId) {
                        $query->where('rooms.id', $roomId);
                    });
            }, 'bedType']);

        // If reservation is not time then check ratePlans
        if (!$isTime) {
            $roomTypes = $roomTypes
                ->where('default_price', '>', 0);
        } else {
            $roomTypes = $roomTypes->where('has_time', 1);
        }

        $roomTypes = $roomTypes->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                $query->unassigned($checkIn, $checkOut);
            }])
            // ->when($roomTypeId, function ($query) use ($roomTypeId) {
            //     return $query->where('id', $roomTypeId);
            // })
            ->get();

        // Calculate enabled and inclusive tax percent
        $taxPercent = $hotel->taxes()
            ->where('is_enabled', true)
            ->where('inclusive', false)
            ->sum('percentage');

        // Get stay nights
        $nights = stayNights($checkIn, $checkOut, false);

        // Calculate roomTypes price 
        foreach ($roomTypes as $item) {
            $ratePlan = $item
                ->ratePlans()
                ->where('is_online_book', true)
                ->first();

            // Calculate new price of reservation
            $rates = [];

            // If has ratePlan
            if ($ratePlan) {
                // Calculate rates
                for ($i = 0; $i < $nights; $i++) {
                    if ($ratePlan->is_daily) {
                        $start = Carbon::parse($checkIn)->addDays($i)->format('Y-m-d');
                        $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                        $rate = $ratePlan->getDailyRate($start, $end);

                        if ($rate) {
                            array_push($rates, $rate->value + calculatePercent($rate->value, $taxPercent));
                        } else {
                            array_push($rates, $item->default_price + calculatePercent($item->default_price, $taxPercent));
                        }
                    }
                }
                $price = array_sum($rates);
            } else {
                $price = $nights * ($item->default_price + calculatePercent($item->default_price, $taxPercent));
            }

            $item->online_price = $price;
        }

        return response()->json([
            'roomTypes' => $roomTypes,
        ]);
    }

    /**
     * Store online book reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeReservation(Request $request)
    {
        // Validation
        $request->validate([
            'slug' => 'required|string',
            'checkIn' => 'required|date_format:Y-m-d H:i',
            'checkOut' => 'required|date_format:Y-m-d H:i',
            'arrivalTime' => 'required|date_format:H:i',
            'guest.name' => 'required|string|max:255',
            'guest.surname' => 'required|string|max:255',
            'guest.phoneNumber' => 'required|numeric|digits:8',
            'guest.email' => 'required|string|email|max:255',
            'guest.passportNumber' => 'nullable|string|max:255',
            'guest.nationality' => 'required|string|max:255',
            'roomTypes' => 'required|array',
            'roomTypes.*.id' => 'required|integer',
            // 'roomTypes.*.numberOfGuests' => 'required|integer',
            // 'roomTypes.*.numberOfChildren' => 'required|integer',
            // 'roomTypes.*.ageOfChildren' => 'nullable|array',
            'roomTypes.*.quantity' => 'required|integer',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Get checkIn and checkOut from request
            $checkIn = $request->input('checkIn');
            $checkOut = $request->input('checkOut');

            // Find hotel
            $hotel = Hotel::whereSlug($request->input('slug'))->first();
            // Find default user
            $user = $hotel->users()->where('is_default', true)->first();
            // Find online book source
            $source = $hotel->sources()
                ->where('is_active', true)
                ->where('service_name', 'onlineBook')
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
                'name' => $request->input('guest.name'),
                'surname' => $request->input('guest.surname'),
                'phone_number' => $request->input('guest.phoneNumber'),
                'email' => $request->input('guest.email'),
                'passport_number' => $request->input('guest.passportNumber'),
                'nationality' => $request->input('guest.nationality'),
                'is_blacklist' => false,
                'hotel_id' => $hotel->id,
            ]);

            $params = [
                'hotelId' => $hotel->id,
                'userCloneId' => $userClone->id,
                'sourceCloneId' => $sourceClone->id,
            ];

            // Create group
            $group = Group::create(['number' => Group::generateUnique(), 'hotel_id' => $hotel->id]);

            foreach ($request->input('roomTypes') as $item) {
                // Find original room type
                $roomType = $hotel->roomTypes()
                    ->where('id', $item['id'])
                    ->firstOrFail();

                // Find online booking rate plan
                $ratePlan = $roomType
                    ->ratePlans()
                    ->where('is_online_book', true)
                    ->first();

                $ratePlanClone = null;

                if ($ratePlan) {
                    // Create rate plan clone
                    $ratePlanClone = RatePlanClone::create([
                        'name' => $ratePlan->name,
                        'is_daily' => $ratePlan->is_daily,
                        'is_ota' => $ratePlan->is_ota,
                        'is_online_book' => $ratePlan->is_online_book,
                        'non_ref' => $ratePlan->non_ref,
                        'rate_plan_id' => $ratePlan->id,
                    ]);
                }

                for ($i = 0; $i < $item['quantity']; $i++) {
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

                    // Rates is default
                    $rates = [];

                    // Get push rates
                    for ($j = 0; $j < $nights; $j++) {
                        $start = Carbon::parse($checkIn)->addDays($j)->format('Y-m-d');
                        $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                        if ($ratePlan) {
                            // if ($ratePlan->is_daily) {
                            $rate = $ratePlan->getDailyRate($start, $end);

                            if ($rate) {
                                array_push($rates, $rate);
                            } else {
                                array_push($rates, ['id' => null, 'date' => $start, 'value' => $roomType->default_price]);
                            }
                            // }
                        } else {
                            array_push($rates, ['id' => null, 'date' => $start, 'value' => $roomType->default_price]);
                        }
                    }

                    // Get numberOfGuests
                    // $numberOfGuests = $item['numberOfGuests'];
                    $numberOfGuests = $roomType->occupancy;

                    // Get numberOfChildren
                    // $numberOfChildren = $item['numberOfChildren'];
                    $numberOfChildren = 0;

                    // Get ageOfChildren
                    // $ageOfChildren = $item['ageOfChildren'];
                    $ageOfChildren = [];

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
                    // if ($numberOfGuests < $roomType->occupancy) {
                    //     $occupancyRatePlan = $ratePlan->occupancyRatePlans()
                    //         ->where('occupancy', $numberOfGuests)
                    //         ->where('is_active', true)
                    //         ->first();
                    // }

                    // Discount
                    $discount = ($occupancyRatePlan != null ? $occupancyRatePlan->discount : 0);

                    // Total amount of rates
                    $amount = array_sum(array_column($rates, 'value')) - ($discount * $nights);

                    $data = array_merge($params, [
                        'stayType' => 'night',
                        'status' => 'pending',
                        'amount' => $amount,
                        'number' => Reservation::generateNumber($request),
                        'numberOfGuests' => $numberOfGuests,
                        'numberOfChildren' => $numberOfChildren,
                        'ratePlanCloneId' => is_null($ratePlanClone) ? null : $ratePlanClone->id,
                        'roomTypeCloneId' => $roomTypeClone->id,
                        'groupId' => $group->id,
                        'checkIn' => $checkIn,
                        'checkOut' => $checkOut,
                        'arrivalTime' => $request->input('arrivalTime'),
                        'exitTime' => '12:00',
                        'statusAt' => Carbon::now(),
                        'postedDate' => $hotel->working_date
                    ]);

                    // Store new reservation
                    $reservation = Reservation::create(snakeCaseKeys($data));

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

                    // Auto arrange room
                    if ($hotel->is_auto_arrange) {
                        $availableRooms = $reservation->availableRooms();
                        // Get first room
                        $firstRoom = $availableRooms->first();

                        // Create room clone
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
            }

            // Send email to hotel reservation email
            if ($hotel->res_email) {
                $user = new User;
                $user->email = $hotel->res_email;
                event(new ReservationCreated($group, $user, true, 'onlineBook'));
            }

            // Send email to guest
            if ($request->filled('guest.email')) {
                event(new ReservationEmailSend($group->id, $request->input('guest.email'), 'reservation', true));
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'group' => $group
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
}
