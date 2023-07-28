<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\{ Hotel, Channel, User, RatePlan, Source, SourceClone, UserClone, Guest, Group, RoomTypeClone, CancellationPercent, CancellationTime, CancellationPolicy, CancellationPolicyClone, DayRate, GuestClone, Room, TaxClone, RoomClone, Cancellation, RatePlanClone };
use App\Traits\{TripTrait};
use App\Events\{ReservationCreated};

class ChannelManagerController extends BaseController
{
    use TripTrait;
    protected $model = 'App\Models\Reservation';
    protected $request = 'App\Http\Requests\ChannelManagerRequest';

    /**
     * Create new property.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function createNewProperty(Request $request, $id)
    {
        try {
            // Find hotel
            $hotel = Hotel::find($id);

            return response()->json([
                'status' => true,
                'data' => $this->syncChannel()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    private function syncChannel() 
    {
        $endpoint = 'https://lef8sbu7r9.execute-api.ap-southeast-1.amazonaws.com/dev/wuBook-createProperty';
        $http = new \GuzzleHttp\Client;

        $response = $http->post($endpoint, [
            'json' => [
                'property' => [
                    'name' => 'Khuvsgul Lake Hotel Test',
                    'address' => 'Baga Toirog',
                    'url' => 'http://www.khuvsgullakehotel.mn/',
                    'zip' => '14200',
                    'city' => 'Ulaanbaatar',
                    'phone' => '+97677068888',
                    'contact_email' => 'reservation@khuvsgullakehotel.mn ',
                    'booking_email' => 'reservation@khuvsgullakehotel.mn ',
                    'country' => 'MN'
                ],
                'account' => [
                    'first_name' => 'Test',
                    'last_name' => 'CTO',
                    'phone' => '999',
                    'lang' => 'EN',
                    'email' => 'futuresonbmb999@gmail.com',
                    'currency' => 'USD'
                ]
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => 'C6opqDG0oi7entlZUPm5Q2hXVhkuB1so2KyqgtQ9'
            ]
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Ctrip associate functions
     */

    /**
     * Integrate to CTrip basic content of hotel.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectHotel(Request $request) 
    {
        $hotel = $request->hotel;
        $count = count($hotel->rooms);

        // Check model has sync id
        if ($hotel->sync_id) {
            try {
                // Send request to ihotel
                $hotel = $this->fetchHotel($hotel);
                if (is_null($hotel)) {
                    return response()->json([
                        'status' => false,
                        'error' => 'Sync id not found.',
                        'message' => 'This hotel is not impossible to connect.',
                    ], 400);
                }

                $hotel->rooms_count = $count;

                // return prepared hotel data
                $sendData = $this->hotelTrait($hotel);
                // Send request to ihotel
                // $http = new \GuzzleHttp\Client;
                // $res = $http->post(config('services.ctrip.baseUrl'), [
                //    'json' => $sendData,
                //     'headers' => [
                //         'Code' => 1591,
                //         'Authorization' => '37245405ebfa7f118ed02f76bbd8ddb2fa3f77e0e1f9d61c2afdf057b29d7516',
                //         'Content-Type' => 'application/json'
                //     ]
                // ]);

                // $data2 = json_decode($res->getBody());

                return response()->json(['success' => true, 'data' => $sendData], 200);
                // return response()->json($data2);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage(), 
                    'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
                ], 400);
            }
        }
    }

    /**
     * Integrate to CTrip basic content of roomTypes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectRoom(Request $request) 
    {
        $hotel = $request->hotel;
        // $languages = ["en-US", "zh-CN"];

        // Check model has sync id
        if ($hotel->sync_id) {
            try {
                // Send request to ihotel
                $http = new \GuzzleHttp\Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/ctrip/hotel/rooms', [
                    'json' => [
                        'id' => $hotel->sync_id,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $data = json_decode($response->getBody());

                $hotelResult = $this->fetchHotel($hotel);
                if (is_null($hotelResult)) {
                    return response()->json([
                        'status' => false,
                        'error' => 'Sync id not found.',
                        'message' => 'This hotel is not impossible to connect.',
                    ], 400);
                }

                $hotel['is_internet'] = $hotelResult->is_internet;

                $roomTypes = $data->roomTypes; // iHotel rooms
                $roomTypesMyHotel = $hotel->roomTypes()->with(['amenities'])->whereNotNull('sync_id')->get();

                // concat myhotel roomtype and ihotel room data
                foreach($roomTypesMyHotel as $r) {
                    foreach($roomTypes as $r2) {
                        if ($r->id == $r2->sync_id) {
                            $r2->children = $r->occupancy_children;
                            $r2->extra_beds = $r->extra_beds;
                            $r2->amenities = $r->amenities;
                            $r2->rooms = count($r->rooms);
                            $r2->floor_size = $r->floor_size;
                            $r2->window = $r->window;
                        }
                    }
                }

                $rate = $this->fetchCurrency('en-US', $hotel);

                $data = $this->room($hotel, $roomTypes, $rate);

                // return prepared hotel data
                $sendData = $data;

                return response()->json($sendData);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => false,
                    'error' => $e->getMessage(),
                    'message' => 'Something went wrong. Please try again.',
                ], 400);
            }
        } else {
            return response()->json([
                'status' => false,
                'error' => 'Sync id not found.',
                'message' => 'This hotel is not impossible to connect.',
            ], 400);
        }
    }

    /**
     * Hotel availability
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availabilityHotel(Request $request) 
    {
        // Validate request
        $messages = [
            'stay.checkIn' => 'The :attribute field is required.'
        ];

        $this->validator($request->all(), 'availabilityRules', $messages);

        try {
            $requestId = $request->input('requestId', null);
            $languageCode = $request->input('languageCode', null);
            $stay = $request->input('stay');
            $numberOfUnits = $request->input('numberOfUnits', 0);
            $checkIn = $stay['checkIn'];
            $checkOut = $stay['checkOut'];
            $responseData = [];

            $hotels = $request->input('hotels', []);

            if (stayNights($checkIn, $checkOut) < 1) {
                return response()->json([
                    'message' => 'Please check that the checkIn and checkOut dates are incorrect.',
                ], 400);
            }

            $currCode = 'USD'; // $languageCode  === 'en-US' ? 'USD' : 'CNY';

            foreach($hotels as $h) {
                $hotel = Hotel::where('id', $h)
                    ->with(['cancellationPolicy'])
                    ->first();

                if ($hotel) {
                    $rate = $this->fetchCurrency($languageCode, $hotel);
                    $hotel->currency = $currCode;
                    $totalRooms = $hotel->rooms()
                    ->availableIn($checkIn, $checkOut)
                    ->count();

                    // Calculate enabled and inclusive tax percent
                    $taxPercent = $hotel->taxes()
                    ->where('is_enabled', true)
                    ->where('inclusive', false)
                    ->sum('percentage');

                    if ($totalRooms >= $numberOfUnits) {
                        $roomTypes = $hotel->roomTypes()
                        ->select('id', 'name', 'short_name', 'occupancy', 'default_price')
                        ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                            $query->unassigned($checkIn, $checkOut);
                        }])
                        ->where('default_price', '>', 0)
                        ->get();

                        foreach($roomTypes as $r) {
                            if (count($r->ratePlans) > 0) {
                                $r->policy = $hotel->cancellationPolicy;
                                $rooms[] = $this->roomType($r, $checkIn, $checkOut, $taxPercent, $rate, $numberOfUnits);
                            }
                        }

                        $info = $this->success($hotel);
                        $info = array_merge($info, [
                            'rooms' => $rooms
                        ]);
                        $responseData[] = $info;
                    } else {
                        $info = $this->noRoom($hotel);
                        $responseData[] = $info;
                    }
                } else {
                    $info = $this->fail($hotel);
                    $responseData[] = $info;
                }
            }

            return response()->json([
                'responseId' => $requestId,
                'hotels' => $responseData
            ]);
        } catch (\RequestException $r) {
            return response()->json([
                'status' => false,
                'message' => 'Went wrong. ' . $r->getMessage(),
            ], 400);
        } catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Hotel RoomType availability
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availabilityRoom(Request $request) 
    {
        // Validate request
        $messages = [
            'stay.checkIn' => 'The :attribute field is required.'
        ];

        $this->validator($request->all(), 'availabilityRules', $messages);

        try {
            $requestId = $request->input('requestId', null);
            $languageCode = $request->input('languageCode', null);
            $stay = $request->input('stay');
            $checkIn = $stay['checkIn'];
            $checkOut = $stay['checkOut'];
            $occupancies = $request->input('occupancies');
            $adults = $occupancies['adults'];
            $children = $occupancies['children'];
            $paxes = $occupancies['paxes'];

            $roomTypeCode = $request->input('roomTypeCode', null);
            $ratePlanCode = $request->input('ratePlanCode', null);

            $numberOfUnits = $request->input('numberOfUnits', 0);
            $responseData = [];

            $hotels = $request->input('hotels', []);

            if (stayNights($checkIn, $checkOut) < 1) {
                return response()->json([
                    'message' => 'Please check that the checkIn and checkOut dates are incorrect.',
                ], 400);
            }

            $currCode = 'USD';

            foreach($hotels as $h) {
                $hotel = Hotel::where('id', $h)
                    ->with(['cancellationPolicy'])
                    ->first();

                if ($hotel) {
                    $hotel->currency = $currCode;
                    $rate = $this->fetchCurrency($languageCode, $hotel);
                    // Calculate enabled and inclusive tax percent
                    $taxPercent = $hotel->taxes()
                    ->where('is_enabled', true)
                    ->where('inclusive', false)
                    ->sum('percentage');

                    $roomType = $hotel->roomTypes()
                        ->select('id', 'name', 'short_name', 'occupancy', 'default_price')
                        ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                            $query->unassigned($checkIn, $checkOut);
                        }])
                        ->where('default_price', '>', 0)
                        // ->where('occupancy', '>=', $adults)
                        // ->where('occupancy_children', '>=', $children)
                        ->where('id', $roomTypeCode)
                        ->first();
                    if ($roomType && $roomType->rooms_count >= $numberOfUnits) {
                        $roomType->policy = $hotel->cancellationPolicy;
                        if ($children > 0) {
                            foreach($paxes as $p) {
                                if ($p['type'] == 'CH') {
                                    $child = $p;
                                }
                            }

                            $childPolicy = $hotel->childrenPolicies()
                            ->where('min', '<=', $child['age'])
                            ->where('max', '>=', $child['age'])
                            ->first();
    
                            if (!is_null($childPolicy)) {
                                $roomType->children_policy = $childPolicy;
                            }
                        }
                        $rooms[] = $this->roomType($roomType, $checkIn, $checkOut, $taxPercent, $rate, $numberOfUnits);
                        $info = $this->success($hotel);
                        $info = array_merge($info, [
                            'rooms' => $rooms
                        ]);
                        $responseData[] = $info;
                    } else {
                        $info = $this->noRoom($hotel);
                        $responseData[] = $info;
                    }
                } else {
                    $info = $this->fail($hotel);
                    $responseData[] = $info;
                }
            }

            return response()->json([
                'responseId' => $requestId,
                'hotels' => $responseData
            ]);
        } catch (\RequestException $r) {
            return response()->json([
                'status' => false,
                'message' => 'Went wrong. ' . $r->getMessage(),
            ], 400);
        } 
        catch(\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create new reservation from ctrip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function booking(Request $request) 
    {
        // Validate request
        $this->validator($request->all(), 'saveReservation');
        $data = $request->all();

        // Find hotel
        $hotel = Hotel::where('id', $request->input('hotelCode'))->first();
        // $hotel = Hotel::where('id', 1)->first();
        $languageCode = $data['languageCode'];

        if($hotel) {
            // MySQL transaction
            $usd = $this->fetchCurrency($languageCode, $hotel);
            DB::beginTransaction();
            $checkIn = $data['stay']['checkIn'];
            $checkOut = $data['stay']['checkOut'];

            if (stayNights($checkIn, $checkOut) < 1) {
                return response()->json([
                    'message' => 'Please check that the checkIn and checkOut dates are incorrect.',
                ], 400);
            }

            $taxPercent = $hotel->taxes()
            ->where('is_enabled', true)
            ->where('inclusive', false)
            ->sum('percentage');

            $totalBeforeTax = 0;
            $totalAfterTax = 0;

            try {
                // Find default user
                $user = $hotel->users()->where('is_default', true)->first();

                // Get stay nights
                $nights = stayNights($checkIn, $checkOut);
                // Find online book source
                $source = $hotel->sources()
                    ->where('service_name', 'ctrip')
                    ->first();

                if ($source === NULL) {
                    return response()->json([
                        'status' => 'Failed',
                        'tripReservationId' => $data['tripReservationId'],
                        'message' => 'The hotel is closed their booking system.',
                    ], 400);
                }

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

                $roomIndexes = [];
                // Create guests checking in
                foreach($data['occupancies'] as $d) {
                    if (array_key_exists('roomIndex', $d)) {
                        $roomIndexes[] = $d['roomIndex'];
                    }
                    $allguests[] = Guest::create([
                        'name' => $d['firstName'],
                        'surname' => $d['lastName'],
                        'phone_number' => '',
                        'email' =>'',
                        'passport_number' => '',
                        'nationality' => '',
                        'is_blacklist' => false,
                        'hotel_id' => $hotel->id,
                    ]);
                }

                $roomIndexes = array_unique($roomIndexes);

                $g = explode(' ', $request->input('contactInfo.guestContact'));
                // Original guest
                $nameCount = count($g);
                $guest = Guest::create([
                    'name' => $g[0],
                    'surname' => $nameCount < 2 ? $g[0] : $g[1],
                    'phone_number' => $request->input('contactInfo.guestPhone'), // decrypt 
                    'email' => $request->input('contactInfo.guestEmail'), // decrypt
                    'passport_number' => '',
                    'nationality' => '',
                    'is_blacklist' => false,
                    'hotel_id' => $hotel->id,
                ]);

                $params = [
                    'hotelId' => $hotel->id,
                    'userCloneId' => $userClone->id,
                    'sourceCloneId' => $sourceClone->id,
                ];

                // Create group
                $group = Group::create(['number' => Group::generateUnique(), 'external_id' => $data['tripReservationId'], 'hotel_id' => $hotel->id]);

                // Find original room type
                $roomType = $hotel->roomTypes()
                ->where('id', $data['roomTypeCode'])
                ->first();

                if (!isset($roomType)) {
                    return response()->json([
                        'status' => 'Failed',
                        'tripReservationId' => $data['tripReservationId'],
                        'message' => 'Room Type Code invalid. ['. $data['roomTypeCode'] . ']',
                    ]);
                }

                // Find online booking rate plan
                $ratePlan = $roomType
                ->ratePlans()
                ->where('id',  $data['ratePlanCode'])
                ->with('meals')
                ->first();

                $totalPenalty = 0;

                if (is_null($ratePlan)) {
                    return response()->json([
                        'status' => 'Failed',
                        'tripReservationId' => $data['tripReservationId'],
                        'message' => 'Rate Plan Code invalid. [' . $data['ratePlanCode'] . ']',
                    ], 400);
                }

                for ($a = 0; $a < $data['numberOfUnits']; $a++) {
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

                    // Rates is default
                    $rates = [];
                    $occupancyRatePlan = null;

                    $numberOfGuests = 0;
                    $numberOfChildren = 0;
                    $ageOfChildren = [];

                    if (!is_null($ratePlan)) {
                        // Create rate plan clone
                        $ratePlanClone = RatePlanClone::create([
                            'name' => $ratePlan->name,
                            'is_daily' => $ratePlan->is_daily,
                            'is_ota' => $ratePlan->is_ota,
                            'is_online_book' => $ratePlan->is_online_book,
                            'non_ref' => $ratePlan->non_ref,
                            'rate_plan_id' => $ratePlan->id,
                        ]);

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
                        }

                        // Get all occupancyRatePlan from ratePlan
                        if ($numberOfGuests < $roomType->occupancy) {
                            $occupancyRatePlan = $ratePlan->occupancyRatePlans()
                                ->where('occupancy', $numberOfGuests)
                                ->where('is_active', true)
                                ->first();
                        }
                    } else {
                        $amount = $roomType->default_price;
                    }

                    foreach($data['occupancies'] as $d) {
                        if($d['type'] == 'AD') {
                            $numberOfGuests += 1;
                        } else {
                            $numberOfChildren += 1;
                            $ageOfChildren[] = $d['age'];
                        }
                    }

                    // Check age of children define number of guests and children
                    if ($numberOfChildren > 0) {
                        foreach ($ageOfChildren as $age) {
                            if ($age > $hotel->age_child) {
                                $numberOfGuests += 1;
                                $numberOfChildren -= 1;
                            }
                        }
                    }

                    // Discount
                    $discount = ($occupancyRatePlan != null ? $occupancyRatePlan->discount : 0);

                    // Total amount of rates
                    // $amount = $rates->sum('value') - ($discount * $nights);
                    $amount = array_sum(array_column($rates, 'value')) - ($discount * $nights);

                    // $amount = $this->convertCurrency($amount / $usd);

                    $resInfo = array_merge($params, [
                        'amount' => $amount,
                        'number' => $this->model::generateNumber($request),
                        'numberOfGuests' => $numberOfGuests,
                        'numberOfChildren' => $numberOfChildren,
                        'ratePlanCloneId' => $ratePlan ? $ratePlanClone->id : 0,
                        'roomTypeCloneId' => $roomTypeClone->id,
                        'groupId' => $group->id,
                        'checkIn' => $checkIn,
                        'checkOut' => $checkOut,
                        'exitTime' => $hotel->check_out_time,
                        'statusAt' => Carbon::now(),
                        'postedDate' => $hotel->working_date
                    ]);

                    // Store new reservation
                    $reservation = $this->model::create(snakeCaseKeys($resInfo));
                    // Create children
                    if ($numberOfChildren > 0) {
                        foreach ($ageOfChildren as $age) {
                            if ($age <= $hotel->age_child) {
                                // Get child policy then access price field
                                $childPolicy = $hotel->childrenPolicies()
                                ->where('min', '<=', $age)
                                ->where('max', '>=', $age)
                                ->get()->first();

                                if (!is_null($childPolicy)) {
                                    $childAmount = $childPolicy->price;
                                    if ($childPolicy->price_type === 'percent') {
                                        $childAmount = $amount / 100 * $childPolicy->price;
                                    }
                                    // $childAmount = $this->convertCurrency($childAmount / $usd);
                                    // Create child
                                    $reservation->children()->create([
                                        'age' => $age,
                                        'amount' => $childAmount,
                                        'reservation_id' => $reservation->id,
                                    ]);
                                }
                            }
                        }
                    }

                    // Create day rates
                    foreach($data['dailyRates'] as $day) {
                        $nightCount = $nights = stayNights($day['periodStartDate'], $day['periodEndDate']);

                        for($i = 0; $i <= $nightCount; $i++) {
                            $date = Carbon::parse($day['periodStartDate'])->addDays($i)->format('Y-m-d');
                            DayRate::create([
                                'date' => $date,
                                'value' => $day['rateBeforeTax'],
                                'default_value' => $day['rateBeforeTax'],
                                'reservation_id' => $reservation->id,
                            ]);
                        }
                    }

                    // Create guests clone checking in
                    foreach($allguests as $g) {
                        GuestClone::create([
                            'name' => $g->name,
                            'surname' => $g->surname,
                            'phone_number' => $g->phone_number,
                            'email' => $g->email,
                            'passport_number' => $g->passport_number,
                            'nationality' => $g->nationality,
                            'description' => $g->description,
                            'is_blacklist' => $g->is_blacklist,
                            'blacklist_reason' => $g->blacklist_reason,
                            'guest_id' => $g->id,
                            'reservation_id' => $reservation->id,
                            'is_primary' => true,
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

                    // $isDirect = count($roomIndexes) > 0 ? true : false;
                    $isDirect = false;
                    if ($isDirect && count($roomIndexes) - 1 <= $a) {
                        $r = $roomIndexes[$a];
                        // Find original room
                        $room = Room::findOrFail($r);
                        // Create room clone
                        $roomClone = RoomClone::create([
                            'name' => $room->name,
                            'ic_name' => $room->ic_name,
                            'status' => $room->status,
                            'description' => $room->description,
                            'room_id' => $room->id,
                        ]);

                        $checkReservation = $this->model::whereDate('check_out', $checkIn)
                        ->where('status', 'checked-in')
                        ->whereHas('roomTypeClone', function ($query) use ($roomType) {
                            $query
                                ->where('room_type_id', $roomType->id);
                        })
                        ->whereHas('roomClone', function ($query) use ($room) {
                            $query
                                ->where('room_id', $room->id);
                        })
                        ->first();

                        if (!is_null($checkReservation)) {
                            $reservation->status = 'pending';
                        }

                        $reservation->roomClone()->associate($roomClone);
                        $reservation->save();

                        $directResId = $reservation->id;
                    } else {
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

                    $reservation->update([
                        'amount' => $reservation->calculate(),
                    ]);

                    $totalBeforeTax = round($reservation->amount); // * $data['numberOfUnits'];

                    // Sync taxes to reservation taxes
                    foreach ($hotel->taxes as $tax) {
                        if ($tax->is_enabled) {
                            // Create tax clones for reservation
                            $taxClone = TaxClone::create([
                                'name' => $tax->name,
                                'percentage' => $tax->percentage,
                                'inclusive' => $tax->inclusive,
                                'key' => $tax->key,
                                'is_default' => $tax->is_default,
                                'is_enabled' => $tax->is_enabled,
                                'reservation_id' => $reservation->id,
                                'tax_id' => $tax->id,
                            ]);

                            if (!$taxClone->inclusive) {
                                // Update reservation amount
                                $reservation = $taxClone->reservation;
                                $reservation->update([
                                    'amount' => $reservation->calculate(),
                                ]);
                            }
                        }
                    }
                    $totalAfterTax = round($reservation->amount);//* $data['numberOfUnits'];
                    // $totalPenalty += $cancellationPolicyClone->cancellationPayment;
                }
                // $totalPenalty += $cancellationPolicyClone->cancellationPayment;
                //$cancellationPolicies[] = $this->computePolicy($roomType, $cancellationPolicy, $roomType->default_price, $usd, $checkIn, $checkOut);
                //$cancellationPolicies[] = $this->computePenaltyPercent($totalAfterTax, $cancellationPolicy, $this->convertCurrency(($roomType->default_price * $data['numberOfUnits']) / $usd), $checkIn, $checkOut);
                //$cancellationPolicies = $this->computePenaltyAmount($roomType->default_price, $cancellationPolicy, $roomType->default_price, $usd, $checkIn, $checkOut, $data['numberOfUnits']);

                $start = Carbon::parse($checkIn)->format('Y-m-d');
                $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                $realend = Carbon::parse($checkOut)->format('Y-m-d');
                $rate = $ratePlan->getDailyRate($start, $end);
                if (!is_null($rate)) {
                    //$policies[] = $this->computePenaltyPercent($data['rateAfterTaxTotal'], $policy, $this->convertCurrency(($rate->value * $numberOfUnits)/ $usd), $start, $end);
                    $cancellationPolicies = $this->computePenaltyAmount(($roomType->default_price), $cancellationPolicy, ($rate->value), $usd, $checkIn, $checkOut, $data['numberOfUnits']);
                } else {
                    //$policies[] = $this->computePenaltyPercent($data['rateAfterTaxTotal'], $policy, $this->convertCurrency(($r->default_price * $numberOfUnits)/ $usd), $start, $end);
                    $cancellationPolicies = $this->computePenaltyAmount(($roomType->default_price), $cancellationPolicy, ($roomType->default_price), $usd, $checkIn, $checkOut, $data['numberOfUnits']);
                }

                $deadline = '';
                if ($cancellationPolicyClone->cancellationTime->has_time) {
                    $deadline = Carbon::createFromFormat('Y-m-d H', $checkIn . ' ' . $cancellationPolicyClone->cancellationTime->day)->toDateTimeString();
                } else {
                    // $deadline = Carbon::parse($checkIn)->subDay($cancellationTime->day)->format('Y-m-d');
                    $deadline = Carbon::parse($checkIn)->addHours(24)->format('Y-m-d');
                }

                // Cancellation Policy
                // $cancellationPolicies = [
                //     [
                //         'penalty' => $totalPenalty,
                //         'deadline' => Carbon::parse($deadline)->format('Y-m-d\TH:i:sP')
                //     ]
                // ];

                $tempPolicies = [];
                foreach($cancellationPolicies as $policy) {
                    if ($policy['penalty'] > 0) {
                        $tempPolicies[] = $policy;
                    }
                }

                if (count($tempPolicies) > 0) {
                } else {
                    $tempPolicies[] = [
                        'penalty' => 99,
                        'deadline'  => Carbon::parse($checkOut)->addDays(1)->format('Y-m-d\TH:i:sP')
                    ];
                }

                $mealCode = 0;
                if($ratePlan->meals->count() > 0) {
                    $mealCode = $this->mealType($ratePlan->meals);
                }

                // Commit transaction
                DB::commit();

                // Send email to hotel reservation email
                if ($hotel->res_email) {
                    $user = new User;
                    $user->email = $hotel->res_email;
                    event(new ReservationCreated($group, $user, true, 'ctrip'));
                }

                // $newRates = [];
                // foreach($data['dailyRates'] as $day) {
                //     $day['rateBeforeTax'] *= $data['numberOfUnits'];
                //     $day['rateAfterTax'] *= $data['numberOfUnits'];
                //     $newRates[]= $day;
                // }

                $rPlan = [];
                $newRates[] = $this->computeRatePlan($roomType, $ratePlan, $checkIn, $checkOut, $taxPercent, $usd, $data["numberOfUnits"]);

                $jsonData = [
                    'status' => $this->statusConvert($reservation->status),
                    'tripReservationId' => $data['tripReservationId'],
                    'supplierReservationId' => $group->number,
                    'hotelConfirmationNo' => '',
                    'totalBeforeTax' => $totalBeforeTax * $data["numberOfUnits"],
                    'totalAfterTax' => $totalAfterTax * $data["numberOfUnits"],
                    'numberOfUnits' => $data['numberOfUnits'],
                    'dailyRates' => $newRates,
                    'cancellationPolicies' => $tempPolicies,
                    // 'group' => $group,
                    // 'reservation' => $reservation
                    // 'isDirect' => $isDirect,
                    // 'resId' => $directResId,
                ];

                if($mealCode > 0) {
                    $jsonData = array_merge($jsonData, [
                        'mealType' => $mealCode,
                    ]);
                }

                return response()->json($jsonData);
            } catch(Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'Failed',
                    'tripReservationId' => $data['tripReservationId'],
                    'message' => 'Something went wrong. Please try again.',
                ], 400);
            }
        }
        return response()->json([
            'status' => 'Failed',
            'tripReservationId' => $data['tripReservationId'],
            'message' => 'Something went wrong. Not Found the hotel',
        ]);
    }

    /**
     * Cancellation reservation from ctrip.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancellation(Request $request) 
    {
        // Validate request
        $this->validator($request->all(), 'cancellation');
        $data = $request->all();
        $hotelCode = $data['hotelCode'];

        $hotel = Hotel::where('id', $hotelCode)->first();
        if($hotel) {

            $usd = $this->fetchCurrency($data['languageCode'], $hotel);
            //$usd = $this->fetchCurrency($languageCode, $hotel);
            // MySQL transaction
            DB::beginTransaction();
            try {
                $group = Group::where('number', $data['supplierReservationId'])->first();
                if (!$group) {
                    return response()->json([
                        'status' => 'Failed',
                        'tripReservationId' => $data['tripReservationId'],
                        'message' => 'Request data invalid, [tripReservationId: ' . $data['tripReservationId'] . '] for reservation id [' . $data['tripReservationId'] . ']',
                    ], 400);
                }
                $reservations = $group->reservations;

                if (count($reservations) <= 0) {
                    return response()->json([
                        'status' => 'Failed',
                        'tripReservationId' => $data['tripReservationId'],
                        'message' => 'Request data invalid, [tripReservationId: ' . $data['tripReservationId'] . '] for reservation id [' . $data['tripReservationId'] . ']',
                    ], 400);
                }

                $totalAmount = 0;
                $totalPenalty = 0;

                $sync_ids = [];

                foreach($reservations as $reservation) {
                    $cancellationPolicyClone = $reservation->cancellationPolicyClone;
                    $roomTypeClone = $reservation->roomTypeClone;
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
                    $totalPenalty += $this->computePenaltyAmountCancellation(
                        $roomTypeClone->default_price,
                        $cancellationPolicyClone, 
                        $roomTypeClone->default_price, 
                        $usd, 
                        $reservation->check_in, 
                        $reservation->check_out, 
                        1
                    );
                    // $totalAmount += $reservation->amount;
                    // $totalPenalty += $cancellationPayment;
                   
                    // Update status changed date
                    if ($status !== $oldStatus) {
                        $reservation->status_at = Carbon::now();
                        $reservation->save();
                    }

                    $reservation->update([
                        'status' => $status,
                        'balance' => $reservation->balance,
                        'is_paid' => false
                    ]);
                    $sync_ids[] = $reservation->sync_id;
                }

                $http = new \GuzzleHttp\Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/delete/blocks', [
                    'json' => [
                        'ids' => $sync_ids,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $respObj = json_decode($response->getBody());

                if(!$respObj->status) { 
                    DB::rollBack();
                    return response()->json([
                        'status' => 'Failed',
                        'tripReservationId' => $data['tripReservationId'],
                        'message' => $respObj->message,
                    ], 400);
                }

                // Commit transaction
                DB::commit();

                return response()->json([
                    'status' => 'Cancelled',
                    'tripReservationId' => $data['tripReservationId'],
                    'penalty' => $totalPenalty,//(int)(($totalPenalty/$totalAmount)*100),
                    'currency' => 'USD',
                ], 200);
            } catch(Exception $e) {
                DB::rollBack();
                return response()->json([
                    'status' => 'Failed',
                    'tripReservationId' => $data['tripReservationId'],
                    'message' => 'Something went wrong. Please try again.',
                ], 400);
            }
        }
        return response()->json([
            'status' => 'Failed',
            'tripReservationId' => $data['tripReservationId'],
            'message' => 'Something went wrong. Please try again.',
        ], 400);
    }

    /**
     * Fetch currencies from capitron
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCurrency($languageCode, $hotel) 
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->get(config('services.ihotel.baseUrl') . '/fetch/currency', [
            'connect_timeout' => 3,
        ]);

        // $rates = $hotel
        //     ->currencies()
        //     ->select(['id', 'name', 'short_name', 'rate', 'is_default'])
        //     ->get()
        //     ->toArray();

        $currCode = $languageCode === 'en-US' ? 'USD' : 'CNY';

        // // $rates = json_decode((string) $response->getBody(), true);

        // $filteredRates = array_filter($rates, function($item) use($currCode) {
        //     return strtoupper($item['short_name']) === $currCode && $item;
        // });

        // $rate = count($filteredRates) === 0 ? 1 : $filteredRates[key($filteredRates)]['rate'];
        $rate = json_decode((string) $response->getBody(), true);

        return $rate;
    }
}
