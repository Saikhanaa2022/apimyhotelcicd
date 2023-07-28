<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    Hotel,
    RoomType,
    CommonLocation,
    RoomClone,
    RoomTypeClone,
    RatePlanClone,
    Group,
    Reservation,
    Cancellation,
    CancellationPolicyClone,
    DayRate,
    Guest,
    GuestClone,
    SourceClone,
    TaxClone,
    User,
    UserClone,
    ResReq,
    ReservedRoomType,
    Partner,
    PartnerClone,
    ReservationPaymentMethod,
    Room
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Events\{ReservationCreated};
use Image;
use App\Traits\ReservationTrait;
use App\Jobs\SendEmail;

class XRoomController extends Controller
{
    use ReservationTrait;
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    // protected $model = 'App\Models\Hotel';
    protected $model = 'App\Models\Reservation';

    /**
     * Return a listing of the hotels.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $customQuery = null)
    {
        try {
            // Search input
            $search = $request->query('search');
            // Pagination page length
            $rowsPerPage = (int) $request->query('rowsPerPage', 12);
            // Sort column
            $sortBy = snake_case($request->query('sortBy', 'id'));
            // Sort direction
            $direction = $request->query('direction', 'desc');

            // Query
            $searchQuery = $customQuery ? $customQuery : Hotel::where([['hotel_type_id', 5], ['has_xroom', 1]])
                ->with('commonLocations');

            // Custom filter
            $searchQuery = $this->filter($searchQuery, $request);

            // Search
            $searchQuery->when($search, function ($query, $search) {
                return $query->search($search);
            });

            // Select columns
            if ($request->has('columns')) {
                $searchQuery = $searchQuery->select($request->query('columns'));
            }

            // // Load relations
            // if ($request->has('with')) {
            //     $searchQuery->with($request->query('with'));
            // }

            if ($request->filled("location")) {
                // dd($request->input('location'));
                $location = CommonLocation::where('id', '=', $request->input('location'))->first();
                if ($location) {
                    $searchQuery->where('common_location_ids', 'LIKE', '%' . $location->id . '%');
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Алдаа гарлаа. Байршил тодорхойгүй.'
                    ], 400);
                }
            }

            $min = $request->input('min');
            $max = $request->input('max');

            if ($min > 0 && $max > 0) {
                $searchQuery = $searchQuery->whereHas('roomTypes', function ($query) use ($min, $max) {
                    $query
                        ->whereHas('xroomType', function ($query) {
                            $query->where('active', 1);
                        })
                        ->whereBetween('default_price', [$min, $max])
                        ->where('price_time', '>', 0)
                        ->with(['bedType'])
                        ->orderBy('price_time', 'ASC');
                })->with([
                        'roomTypes' => function ($query) use ($min, $max) {
                            $query
                                ->whereHas('xroomType', function ($query) {
                                    $query->where('active', 1);
                                })
                                ->whereBetween('default_price', [$min, $max])
                                ->where('price_time', '>', 0)
                                ->with(['bedType'])
                                ->orderBy('price_time', 'ASC');
                        }
                    ]);
            } else {
                $searchQuery = $searchQuery->whereHas('roomTypes', function ($query) {
                    $query
                        ->whereHas('xroomType', function ($query) {
                            $query->where('active', 1);
                        })
                        ->where('default_price', '>', 0)
                        ->where('price_time', '>', 0)
                        ->with(['bedType'])
                        ->orderBy('price_time', 'ASC');
                })->with([
                        'roomTypes' => function ($query) use ($min, $max) {
                            $query
                                ->whereHas('xroomType', function ($query) {
                                    $query->where('active', 1);
                                })
                                ->where('default_price', '>', 0)
                                ->where('price_time', '>', 0)
                                ->with(['bedType'])
                                ->orderBy('price_time', 'ASC');
                        }
                    ]);
            }

            // Load with counts model
            // if ($request->has('withCounts')) {
            //     $searchQuery->withCount($request->query('withCounts'));
            // }

            // Sort
            $searchQuery->orderBy($sortBy, $direction);

            if ($rowsPerPage === 0) {
                return response()->json($searchQuery->get());
            }

            if ($rowsPerPage === -1) {
                $rowsPerPage = $searchQuery->count();
            }

            return response()->json($searchQuery->paginate($rowsPerPage));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Return a listing of the hotels.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHotelsMap(Request $request, $customQuery = null)
    {
        try {
            // Search input
            $search = $request->query('search');
            // Pagination page length
            $rowsPerPage = (int) $request->query('rowsPerPage', 12);
            // Sort column
            $sortBy = snake_case($request->query('sortBy', 'id'));
            // Sort direction
            $direction = $request->query('direction', 'asc');

            // Query
            $searchQuery = $customQuery ? $customQuery : Hotel::where([['hotel_type_id', 5], ['has_xroom', 1]])
                ->whereRaw('location is not null')
                ->with('commonLocations');

            // Custom filter
            $searchQuery = $this->filter($searchQuery, $request);

            // Search
            $searchQuery->when($search, function ($query, $search) {
                return $query->search($search);
            });

            // Select columns
            if ($request->has('columns')) {
                $searchQuery = $searchQuery->select($request->query('columns'));
            }

            $min = $request->input('min');
            $max = $request->input('max');

            if ($min > 0 && $max > 0) {
                $searchQuery = $searchQuery->whereHas('roomTypes', function ($query) use ($min, $max) {
                    $query
                        ->whereHas('xroomType', function ($query) {
                            $query->where('active', 1);
                        })
                        ->whereBetween('default_price', [$min, $max])
                        ->where('price_time', '>', 0)
                        ->with(['bedType'])
                        ->orderBy('price_time', 'ASC');
                })->with([
                        'roomTypes' => function ($query) use ($min, $max) {
                            $query
                                ->whereHas('xroomType', function ($query) {
                                    $query->where('active', 1);
                                })
                                ->whereBetween('default_price', [$min, $max])
                                ->where('price_time', '>', 0)
                                ->with(['bedType'])
                                ->orderBy('price_time', 'ASC');
                        }
                    ]);
            } else {
                $searchQuery = $searchQuery->with([
                    'roomTypes' => function ($query) {
                        $query
                            ->whereHas('xroomType', function ($query) {
                                $query->where('active', 1);
                            })
                            ->where('default_price', '>', 0)
                            ->where('price_time', '>', 0)
                            ->with(['bedType'])
                            ->orderBy('price_time', 'ASC');
                    }
                ]);
            }

            // Sort
            $searchQuery->orderBy($sortBy, $direction);

            if ($rowsPerPage === 0) {
                return response()->json($searchQuery->get());
            }

            if ($rowsPerPage === -1) {
                $rowsPerPage = $searchQuery->count();
            }

            return response()->json($searchQuery->paginate($rowsPerPage));
        } catch (\Exception $e) {
            return response()->json([
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
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

        return $query;
    }

    /**
     * Return a roomTypes of the hotels.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getHotel(Request $request, $hoteld = 0)
    {
        try {
            if ($hoteld != 0) {
                $h = Hotel::select('id', 'sync_id', 'name', 'description', 'address', 'location', 'images', 'email', 'phone', 'slug', 'check_in_time', 'check_out_time', 'working_date', 'has_ihotel', 'has_online_book', 'has_chatbot')
                    ->find($hoteld);

                return response()->json([
                    'hotelData' => [$h],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Буудал олдсонгүй.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Return a listing of the common locations.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCommonLocations(Request $request)
    {
        try {

            $common_locations = CommonLocation::select('id', 'name', 'name_en', 'description', 'district_id', 'slug', 'longitude_latitude')
                ->whereHas('hotels')
                ->get();

            return response()->json([
                'success' => true,
                'commonLocations' => $common_locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * find available roomTypes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchRoomTypes(Request $request)
    {
        try {
            if ($request->has('hotelId') && $request->input('hotelId', 0) != 0) {
                // Get stay type
                $stayType = $request->input('stayType', 'night');

                // Validation
                $request->validate([
                    'checkIn' => 'required|date_format:Y-m-d H:i',
                    'checkOut' => 'required|date_format:Y-m-d H:i',
                    'resTime' => ($stayType === 'time' ? 'required' : 'nullable') . '|integer',
                    // 'partner.id' => 'nullable|integer',
                    'stayType' => 'required|in:night,day,time',
                ]);

                $checkIn = $request->input('checkIn');
                $checkOut = $request->input('checkOut');
                $resTime = (int) $request->input('resTime');

                if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut)) || stayNights($checkIn, $checkOut, true) < 1) {
                    Log::info('checkin: ' . $checkIn);
                    Log::info('checkout: ' . $checkOut);
                    die('checkin: ' . $checkIn . ' checkout:  ' . $checkOut);
                    return response()->json([
                        'success' => false,
                        'message' => 'Ирэх болон Гарах огноо буруу байгаа тул шалгана уу.',
                    ], 400);
                }

                if ($stayType === 'time') {
                    $checkOut = Carbon::parse($checkIn)
                        ->addHour($resTime)
                        ->format('Y-m-d H:i');
                }

                $roomId = $request->input('roomId');
                $roomTypeId = $request->input('roomTypeId');

                // Check room types
                $roomTypes = RoomType::select('id', 'name', 'occupancy', 'occupancy_children', 'price_day_use', 'price_time', 'price_time_count', 'default_price', 'short_name', 'bed_type_id', 'images', 'discount_percent')
                    ->whereHas('xroomType', function ($query) {
                        $query->where('active', 1);
                    })
                    ->where('hotel_id', $request->input('hotelId'))
                    ->where('default_price', '>', 0)
                    ->where('price_time', '>', 0)
                    ->with([
                        'rooms' => function ($query) use ($checkIn, $checkOut, $roomId) {
                            $query->select(['id', 'name', 'status', 'description', 'room_type_id'])
                                ->unassigned($checkIn, $checkOut)
                                ->when($roomId, function ($query) use ($roomId) {
                                    $query->where('rooms.id', $roomId);
                                });
                        }
                    ])
                    ->with(['xroomType'])
                    ->with(['bedType'])
                    ->with(['amenities'])
                    ->orderBy('price_time', 'asc');

                // If reservation is not time then check ratePlans
                if ($stayType === 'night') {
                    $roomTypes = $roomTypes->withAndwhereHas('ratePlans', function ($query) use ($checkIn, $checkOut) { //, $partner
                    })->where('default_price', '>', 0);
                } else {
                    $roomTypes = $roomTypes->where([['has_time', true], ['price_time', '>', 0]])
                        ->where('price_time_count', '=', $resTime);
                }

                $roomTypes = $roomTypes->withCount([
                    'rooms' => function ($query) use ($checkIn, $checkOut) {
                        $query->unassigned($checkIn, $checkOut);
                    }
                ])
                    ->when($roomTypeId, function ($query) use ($roomTypeId) {
                        return $query->where('id', $roomTypeId);
                    })
                    ->havingRaw('rooms_count > 0')
                    ->get();

                // Calc room type rate price
                if ($stayType === 'night') {
                    $nights = stayNights($checkIn, $checkOut, false);

                    foreach ($roomTypes as $roomType) {
                        foreach ($roomType->ratePlans as $ratePlan) {
                            $rates = [];
                            $defaultPrice = $roomType->default_price;

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

                            $ratePlan->totalPrice = $this->calcRates($rates, true);
                        }
                    }
                }

                foreach ($roomTypes as $roomType) {
                    $reservations = Reservation::where([['check_in', '=', $checkIn], ['check_out', '=', $checkOut], ['status', '!=', 'pending'], ['status', '!=', 'canceled'], ['status', '!=', 'checked-out']])
                        ->whereHas('roomTypeClone', function ($query) use ($roomType) {
                            $query->where('room_type_id', $roomType->id);
                        })->get();

                    if ($roomType->xroomType->sale_quantity > $reservations->count()) {
                        $roomType->xroomType->sale_quantity = $roomType->xroomType->sale_quantity - $reservations->count();
                    } else {
                        $roomType->xroomType->sale_quantity = 0;
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => $roomTypes,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Буудал олдсонгүй. ' . $request->input('hotelId'),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Calculate rates.
     *
     * @param  Array|List  $dayRates
     * @param  Boolean $isArray
     * @return $total
     */
    private function calcRates($dayRates = null, $isArray = false)
    {
        if (!$isArray) {
            $amount = $dayRates
                ? $dayRates->sum('value')
                : [];
        } else {
            $amount = array_sum(array_column($dayRates, 'value'));
        }

        // Amount
        $total = $amount;

        return $total;
    }

    /**
     * Create xroom reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createReservation(Request $request)
    {
        // Validate request
        // $this->validator($request->all(), '');

        // MySQL transaction
        DB::beginTransaction();

        try {
            $checkIn = $request->input('checkIn');
            $checkOut = $request->input('checkOut');
            $stayType = $request->input('stayType', 'night');
            $isSendEmail = $request->input('isSendEmail', false);
            $totalAmount = 0;
            // Get stay hours
            $resTime = (int) $request->input('resTime');
            // Use this variable when reservation is direct
            $directResId = 0;

            if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut)) || stayNights($checkIn, $checkOut, true) < 1) {
                Log::info('checkin: ' . $checkIn);
                Log::info('checkout: ' . $checkOut);
                die('checkin: ' . $checkIn . ' checkout:  ' . $checkOut);
                return response()->json([
                    'success' => false,
                    'message' => 'Ирэх болон Гарах огноо буруу байгаа тул шалгана уу.',
                ], 400);
            }

            // Find hotel
            $hotel = Hotel::find($request->input('hotelId'));
            if ($hotel === NULL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Буудлын мэдээлэл олдсонгүй.',
                ], 400);
            }

            $paymentMethod = $request->input('paymentMethod', null);

            // if ($paymentMethod == "card") {
            //     $cardNumber = $request->get('card_number');
            //     $cardName = $request->get('card_holders_name');
            //     $expiryMonth = $request->get('expired_month');
            //     $expiryYear = $request->get('expired_year');
            //     $cvc = $request->get('cvc');
            //     $cardData = [
            //         'cardnumber' => $cardNumber, 
            //         'cardname' => $cardName, 
            //         'expirymonth' => $expiryMonth, 
            //         'expiryyear' => $expiryYear, 
            //         'cvv' => $cvc,
            //     ];
            // }

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
                return response()->json([
                    'success' => false,
                    'message' => 'Захиалгын суваг холбогдоогүй байна.',
                ], 400);
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

            // Find original partner
            $partner = Partner::find($request->input('partner.id'));

            // Original guest
            $guest = Guest::firstOrCreate(
                [
                    'id' => $request->input('guest.id'),
                    'hotel_id' => $hotel->id,
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
                'arrivalTime',
                'notes',
                'status',
                'stayType'
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
                // Find original room type
                $roomType = $hotel->roomTypes()
                    ->where('id', $item['id'])
                    ->whereHas('xroomType', function ($query) {
                        $query->where('active', 1);
                    })
                    ->first();

                if (is_null($roomType)) {
                    return response()->json([
                        'success' => false,
                        'message' => $item['id'] . ' холбогдоогүй байна.',
                    ], 400);
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
                        ->where('id', $item['ratePlan']['id'])
                        // ->availableIn($reqCheckIn, $reqCheckOut)
                        ->first();

                    if (is_null($ratePlan)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'ratePlan тохируулаагүй байна.',
                        ], 400);
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
                                            'success' => false,
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
                            $room = Room::findOrFail($request->input('roomId'));

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

                        // Update reservation amount
                        $reservation->update([
                            'amount' => $reservation->calculate(),
                        ]);

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
                        $totalAmount += $reservation->amount;
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
                        // $amount = $item['timePrice'];
                        $amount = $roomType->price_time;

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

                            // Create room clone
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

                        // Update reservation amount
                        $reservation->update([
                            'amount' => $reservation->calculate(),
                        ]);

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
                        $totalAmount += $reservation->amount;
                    }
                }
            }
            $http = new \GuzzleHttp\Client;
            $token = $this->generateToken();
            $resId = $this->generateResId();
            $number = 'TR-' . $reservation->group_id; // 'XR-' . $reservation->group_id;
            $reservationPaymentMethod = null;
            if (config('app.env') == 'production') {
                // Lend mn
                if ($paymentMethod == "lendmn") {
                    $response = $http->post(config('services.lendmn.url') . '/w/invoices', [
                        'headers' => [
                            'Content-Type' => 'application/x-www-form-urlencoded',
                            'x-and-auth-token' => config('services.lendmn.token'),
                        ],
                        'verify' => false,
                        'form_params' => [
                            'amount' => $totalAmount,
                            // / 100 * 99,
                            'description' => 'Айхотел төлбөр',
                            'duration' => '172800'
                        ],
                    ]);

                    $lendResponse = json_decode((string) $response->getBody(), true);

                    $reservation->lend_invoice_number = $lendResponse['response']['invoiceNumber'];
                    $reservation->lend_qr_string = $lendResponse['response']['qr_string'];
                    $reservation->lend_url = $lendResponse['response']['qr_link'];
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
                        'lend_invoice_number' => $lendResponse['response']['invoiceNumber'],
                        'lend_qr_string' => $lendResponse['response']['qr_string'],
                        'lend_url' => $lendResponse['response']['qr_link'],
                        'token' => $token,
                        'hotel_id' => $hotel->id
                    ]);
                    $reservationPaymentMethod->save();
                }

                //Mongol chat
                if ($paymentMethod == "mongolchat") {
                    $products = [
                        'product_name' => '' . $number,
                        'quantity' => '1',
                        'price' => $totalAmount,
                    ];

                    $params = [
                        'amount' => $totalAmount,
                        'products' => [$products],
                        'title' => 'Xroom',
                        'sub_title' => '',
                        'noat' => '',
                        'nhat' => '',
                        'ttd' => '',
                        'reference_number' => $reservation->group_id,
                        'expire_time' => '720'
                    ];

                    $requestUrl = config('services.mongolchat.base_url') . config('services.mongolchat.prefix') . '/worker/onlineqr/generate';

                    $response = $http->post($requestUrl, [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => config('services.mongolchat.worker_auth'),
                            'api-key' => config('services.mongolchat.api_key'),
                        ],
                        'body' => json_encode($params),
                    ]);

                    $mcResponse = json_decode((string) $response->getBody(), true);

                    // Check is success
                    if ($mcResponse['code'] === 1000) {
                        $reservation->mc_qrcode = $mcResponse['qr'];
                        $reservation->resId = $resId;
                        $reservation->token = $token;
                    }

                    // create middle table
                    $reservationPaymentMethod = ReservationPaymentMethod::create([
                        'res_id' => $resId,
                        'group_id' => $reservation->group_id,
                        'number' => $number,
                        'reservation_id' => $reservation->id,
                        'payment_method' => $paymentMethod,
                        'amount' => $totalAmount,
                        'mc_qrcode' => $mcResponse['qr'],
                        'token' => $token,
                        'hotel_id' => $hotel->id
                    ]);
                    $reservationPaymentMethod->save();
                }

                // Qpay
                if ($paymentMethod == "qpay") {
                    $response = $http->post(url('qpay/generate/invoice'), [
                        'headers' => [
                            'access-key' => config('services.xroom.access_key'),
                        ],
                        'form_params' => [
                            'number' => $number,
                            'description' => 'Захиалгын төлбөр',
                            'amount' => $totalAmount,
                        ],
                    ]);

                    $qpayResponse = json_decode((string) $response->getBody(), true);

                    // if (!is_dir('img/uploads')) {
                    //     mkdir('img/uploads', 0755, true);
                    // }

                    // if (!is_dir('img/uploads/qr')) {
                    //     mkdir('img/uploads/qr', 0755, true);
                    // }

                    // $qrImageUrl = 'img/uploads/qr/'.md5(microtime()).'.png';
                    // Image::make(file_get_contents('data:image/png;base64,' . $qpayResponse['qr_image']))
                    //     ->save($qrImageUrl);

                    $image = base64_decode($qpayResponse['qr_image']);
                    $path = 'qpayqr/' . md5(microtime()) . '.png';

                    $storagePath = Storage::put($path, $image, 'public');

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
            }

            // Commit transaction
            DB::commit();
            // Hotel and XRoom mail to send email
            if ($hotel->has_xroom) {
                if ($hotel->res_email) {
                    $user = new User;
                    $user->email = $hotel->res_email;
                    event(new ReservationCreated($group, $user, true, 'xroom'));
                }
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

            return response()->json([
                'success' => true,
                'group' => $group,
                'isDirect' => $isDirect,
                'resId' => $directResId,
                'reservation' => $reservation,
                'reservationPaymentMethod' => $reservationPaymentMethod,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Get New Payment Invoice
     */
    protected function createNewInvoice(Request $request)
    {
        $token = $request->input('token', null);
        $paymentMethod = $request->input('method', null);
        $oldMethod = $request->input('oldMethod', null);
        $groupId = $request->input('id', null);

        if (is_null($token) || is_null($paymentMethod) || is_null($oldMethod) || is_null($groupId)) {
            return response()->json([
                'success' => false,
                'message' => 'Хүсэлт хоосон утга агуулсан байна!',
            ], 400);
        }

        $reservation = Reservation::where([['group_id', $groupId], ['status', 'pending']])->first();
        if (is_null($reservation)) {
            return response()->json([
                'success' => false,
                'message' => 'Нэхэмжлэл үүсгэх боломжгүй захиалга байна.',
            ], 400);
        }

        // dd($reservation);

        $invoice = ReservationPaymentMethod::where([['group_id', $groupId], ['reservation_id', $reservation->id]])->first();
        if (is_null($invoice)) {
            return response()->json([
                'success' => false,
                'message' => 'Төлбөрийн нэхэмжлэл үүсээгүй байна.',
            ], 400);
        }

        if ($invoice->paid) {
            return response()->json([
                'success' => true,
                'message' => 'Төлбөр төлөгдсөн байна.',
            ], 200);
        }

        $oldMethod = $invoice->payment_method;
        $isUpdate = false;

        if ($invoice->lend_qr_string && $paymentMethod == 'lendmn') {
            $invoice->payment_method = $paymentMethod;
            $isUpdate = true;
        } else if ($invoice->qpay_qr_text && $paymentMethod == 'qpay') {
            $invoice->payment_method = $paymentMethod;
            $isUpdate = true;
        } else if ($invoice->mc_qrcode && $paymentMethod == 'mongolchat') {
            $invoice->payment_method = $paymentMethod;
            $isUpdate = true;
        }

        // MySQL transaction
        DB::beginTransaction();
        // Payment Process
        $http = new \GuzzleHttp\Client;
        $reservationPaymentMethod = null;
        try {
            if (!$isUpdate) {
                if (config('app.env') == 'production') {
                    // Lend mn
                    if ($paymentMethod == "lendmn" && $oldMethod != "lendmn") {
                        $response = $http->post(config('services.lendmn.url') . '/w/invoices', [
                            'headers' => [
                                'Content-Type' => 'application/x-www-form-urlencoded',
                                'x-and-auth-token' => config('services.lendmn.token'),
                            ],
                            'verify' => false,
                            'form_params' => [
                                'amount' => $invoice->amount,
                                // / 100 * 99,
                                'description' => 'Айхотел төлбөр',
                                'duration' => '172800'
                            ],
                        ]);

                        $lendResponse = json_decode((string) $response->getBody(), true);

                        $reservation->lend_invoice_number = $lendResponse['response']['invoiceNumber'];
                        $reservation->lend_qr_string = $lendResponse['response']['qr_string'];
                        $reservation->lend_url = $lendResponse['response']['qr_link'];
                        $reservation->resId = $invoice->res_id;
                        $reservation->token = $invoice->token;

                        $invoice->update([
                            'payment_method' => $paymentMethod,
                            'lend_invoice_number' => $lendResponse['response']['invoiceNumber'],
                            'lend_qr_string' => $lendResponse['response']['qr_string'],
                            'lend_url' => $lendResponse['response']['qr_link']
                        ]);
                    }

                    //Mongol chat
                    if ($paymentMethod == "mongolchat" && $oldMethod != "mongolchat") {
                        $products = [
                            'product_name' => '' . $invoice->number,
                            'quantity' => '1',
                            'price' => $invoice->amount,
                        ];

                        $params = [
                            'amount' => $invoice->amount,
                            'products' => [$products],
                            'title' => 'Xroom',
                            'sub_title' => '',
                            'noat' => '',
                            'nhat' => '',
                            'ttd' => '',
                            'reference_number' => $reservation->group_id,
                            'expire_time' => '720'
                        ];

                        $requestUrl = config('services.mongolchat.base_url') . config('services.mongolchat.prefix') . '/worker/onlineqr/generate';

                        $response = $http->post($requestUrl, [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Authorization' => config('services.mongolchat.worker_auth'),
                                'api-key' => config('services.mongolchat.api_key'),
                            ],
                            'body' => json_encode($params),
                        ]);

                        $mcResponse = json_decode((string) $response->getBody(), true);

                        // Check is success
                        if ($mcResponse['code'] === 1000) {
                            $reservation->mc_qrcode = $mcResponse['qr'];
                            $reservation->resId = $invoice->res_id;
                            $reservation->token = $invoice->token;
                        }

                        // create middle table
                        $invoice->update([
                            'payment_method' => $paymentMethod,
                            'mc_qrcode' => $mcResponse['qr']
                        ]);
                    }

                    // Qpay
                    if ($paymentMethod == "qpay" && $oldMethod != "qpay") {
                        $response = $http->post(url('qpay/generate/invoice'), [
                            'headers' => [
                                'access-key' => config('services.xroom.access_key'),
                            ],
                            'form_params' => [
                                'number' => $invoice->number,
                                'description' => 'Захиалгын төлбөр',
                                'amount' => $invoice->amount,
                            ],
                        ]);

                        $qpayResponse = json_decode((string) $response->getBody(), true);
                        $image = base64_decode($qpayResponse['qr_image']);
                        $path = 'qpayqr/' . md5(microtime()) . '.png';

                        $storagePath = Storage::put($path, $image, 'public');

                        $reservation->qpay_invoice_id = $qpayResponse['invoice_id'];
                        $reservation->qpay_qrcode = $qpayResponse['qr_text'];
                        $reservation->qpay_qrimage_base64 = $qpayResponse['qr_image'];
                        $reservation->qpay_qrimage = $path;
                        $reservation->qpay_url = $qpayResponse['qPay_shortUrl'];
                        $reservation->qpay_urls = $qpayResponse['urls'];
                        $reservation->resId = $invoice->res_id;
                        $reservation->token = $invoice->token;

                        // create middle table
                        $invoice->update([
                            'payment_method' => $paymentMethod,
                            'qpay_invoice_id' => $qpayResponse['invoice_id'],
                            'qpay_qrcode' => $qpayResponse['qr_text'],
                            'qpay_qr_text' => $qpayResponse['qr_text'],
                            'qpay_qrimage_base64' => $qpayResponse['qr_image'],
                            'qpay_qrimage' => $path,
                            'qpay_url' => $qpayResponse['qPay_shortUrl'],
                            'qpay_urls' => serialize($qpayResponse['urls'])
                        ]);
                    }
                }
            }

            $invoice->save();
            $userId = $reservation->guestClone->phone_number;
            if (config('app.env') == 'production') {
                $this->updateXroomReservation($userId, $invoice->res_id, "pending", 0, $invoice->payment_method);
            }
            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'resId' => $invoice->res_id,
                'reservation' => $reservation,
                'invoice' => $invoice
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }



    /**
     * LendMN
     * check reservation by lend
     */
    public function checkReservationPaymentLend($id)
    {
        try {
            $reservationPaymentMethod = ReservationPaymentMethod::where('group_id', $id)->firstOrFail();
            $reservation = Reservation::where('group_id', $id)->firstOrFail();
            $http = new \GuzzleHttp\Client;

            $response = $http->post(url('lend/check/payment'), [
                'headers' => [

                    'access-key' => config('services.xroom.access_key'),
                ],
                'verify' => false,
                'form_params' => [
                    'number' => $reservationPaymentMethod->lend_invoice_number,
                ],
            ]);

            $lendResponse = json_decode((string) $response->getBody(), true);
            $success = ($lendResponse['code'] === 0 && $lendResponse['response']['status'] === 1)
                ? true
                : false;

            // Check is paid
            if ($success) {
                $paymentMethod = $reservationPaymentMethod->payment_method;
                // Confirm reservation
                $this->confirmReservation($reservationPaymentMethod, $paymentMethod, $lendResponse);
            }

            return response()->json([
                'success' => $success,
                'lendResponse' => $lendResponse,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => $success,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
    /**
     * Show Payment Qr LendMN
     */
    public function showReservationPaymentLend($id)
    {
        try {
            $reservation = ReservationPaymentMethod::whereNotNull('lend_qr_string')
                ->where([['group_id', $id], ['payment_method', 'lendmn']])->firstOrFail();

            $data = Reservation::where('group_id', $id)->select('id', 'group_id', 'hotel_id', 'check_in', 'check_out', 'created_at', 'amount')->with([
                "hotel" => function ($query) {
                    $query->select('id', 'name', 'address', 'phone', 'res_phone');
                }
            ])->first();

            $user = GuestClone::where('reservation_id', $data->id)->first();

            return response()->json([
                'success' => true,
                'reservation' => $reservation,
                'data' => $data,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * QPay
     * Show Payment Qr QPay
     */
    public function showReservationPayment($id)
    {
        try {
            $reservation = ReservationPaymentMethod::where([['group_id', $id], ['payment_method', 'qpay']])->firstOrFail();

            $reservation->qpay_urls = unserialize($reservation->qpay_urls);

            $data = Reservation::where('group_id', $id)->select('id', 'group_id', 'hotel_id', 'check_in', 'check_out', 'created_at')->with([
                "hotel" => function ($query) {
                    $query->select('id', 'name', 'address', 'phone', 'res_phone');
                }
            ])->first();
            $user = GuestClone::where('reservation_id', $data->id)->first();

            return response()->json([
                'success' => true,
                'reservation' => $reservation,
                'data' => $data,
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * check reservation by QPay
     */
    public function checkReservationPayment($id)
    {
        $reservationPaymentMethod = ReservationPaymentMethod::where('group_id', $id)->firstOrFail();
        try {
            $http = new \GuzzleHttp\Client;

            $response = $http->post(url('qpay/check/payment'), [
                'headers' => [
                    'access-key' => config('services.xroom.access_key'),
                ],
                'form_params' => [
                    'number' => $reservationPaymentMethod->qpay_invoice_id,
                ],
            ]);

            $qpayResponse = json_decode((string) $response->getBody(), true);

            $success = false;

            if ($qpayResponse['count'] != 0) {
                $reservationPaymentMethod->qpay_transaction = serialize($qpayResponse['rows']);
                $reservationPaymentMethod->update();

                // Confirm reservation
                $this->confirmReservation($reservationPaymentMethod, 'qpay', $qpayResponse);

                $success = true;
            }

            return response()->json([
                'success' => $success,
                'response' => $qpayResponse
            ], 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * MongolChat
     * Show Payment Qr MongolChat
     */
    public function showReservationPaymentMC($id)
    {
        try {
            $reservation = ReservationPaymentMethod::whereNotNull('mc_qrcode')->where([['group_id', $id], ['payment_method', 'mongolchat']])->firstOrFail();

            $data = Reservation::where('group_id', $id)->select('id', 'group_id', 'hotel_id', 'check_in', 'check_out', 'created_at')->with([
                "hotel" => function ($query) {
                    $query->select('id', 'name', 'address', 'phone', 'res_phone');
                }
            ])->first();
            $user = GuestClone::where('reservation_id', $data->id)->first();

            return response()->json([
                'success' => true,
                'reservation' => $reservation,
                'data' => $data,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    // Check mongol chat payment
    public function checkReservationPaymentMc($id)
    {
        try {
            $reservationPaymentMethod = ReservationPaymentMethod::where('group_id', $id)->firstOrFail();
            $http = new \GuzzleHttp\Client;

            $params = [
                'qr' => $reservationPaymentMethod->mc_qrcode,
            ];
            $requestUrl = config('services.mongolchat.base_url') . config('services.mongolchat.prefix') . '/worker/onlineqr/status';

            $response = $http->post($requestUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => config('services.mongolchat.worker_auth'),
                    'api-key' => config('services.mongolchat.api_key'),
                ],
                'body' => json_encode($params),
            ]);

            $mcResponse = json_decode((string) $response->getBody(), true);
            $success = false;

            if ($mcResponse['code'] === 1000) {
                switch ($mcResponse['status']) {
                    case 'paid':
                        $success = true;
                        $reservationPaymentMethod->mc_trans_id = $mcResponse['id'];
                        $reservationPaymentMethod->update();
                        // Confirm reservation
                        $this->confirmReservation($reservationPaymentMethod, 'mongolchat', $mcResponse);
                        break;

                    case 'unpaid':
                        $success = false;
                        break;

                    case 'expired':
                        $success = false;
                        break;

                    default:
                        $success = false;
                        break;
                }
            } else {
                $success = false;
            }

            return response()->json([
                'success' => $success,
                'response' => $mcResponse
            ], 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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

    // cancel Reservation
    public function cancelReservation($id)
    {
        try {
            $reservationPaymentMethod = ReservationPaymentMethod::where('group_id', $id)->firstOrFail();
            $reservations = Reservation::where("group_id", $id)->get();

            // cancelReservations
            $this->cancelOrder($id, $reservationPaymentMethod);

            // cancelInvoices
            if ($reservationPaymentMethod->lend_qr_string && $reservationPaymentMethod->paid === 0) {
                $http = new \GuzzleHttp\Client;

                $response = $http->post(url('lend/cancel/invoice'), [
                    'headers' => [
                        'access-key' => config('services.xroom.access_key'),
                    ],
                    'verify' => false,
                    'form_params' => [
                        'number' => $reservationPaymentMethod->lend_invoice_number,
                    ],
                ]);
            } else if ($reservationPaymentMethod->qpay_qrcode && $reservationPaymentMethod->paid === 0) {
                $this->cancelQpayInvoice($reservationPaymentMethod->qpay_invoice_id);
            }

            return response()->json([
                'success' => true,
                'reservations' => $reservations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    public function cancelQpayInvoice($id)
    {
        $http = new \GuzzleHttp\Client;

        $response = $http->post(config('services.qpay.base_url') . '/auth/token', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(config('services.qpay.account_v2')),
            ]
        ]);

        $tokenResponse = json_decode((string) $response->getBody(), true);

        $response = $http->delete(config('services.qpay.base_url') . '/invoice/' . $id, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $tokenResponse['access_token'],
            ]
        ]);
    }

    public function getReservations(Request $request)
    {
        try {

            $rowsPerPage = $request->input('perPage', 0);
            $links = '';
            $reservations = ReservationPaymentMethod::with([
                'reservation' => function ($query) {
                    $query->select(
                        'id',
                        'number',
                        'hotel_id',
                        'group_id',
                        'stay_type',
                        'check_in',
                        'check_out',
                        'amount',
                        'status'
                    )
                        ->with([
                            'hotel' => function ($query) {
                                $query->select('id', 'name')
                                    ->with('hotelBanks');
                            }
                        ])
                        ->with('guestClone')
                        ->with([
                            'group' => function ($query) {
                                $query->select('id', 'number', 'hotel_id');
                            }
                        ]);
                }
            ])->get();

            // if ($rowsPerPage > 0) {
            //     $reservations = $reservations->paginate($rowsPerPage);
            //     $links = $reservations->links();
            // } else {
            //     $reservations = $reservations->get();
            // }

            return response()->json([
                'success' => true,
                'reservations' => $reservations,
                // 'links' => $links,
            ], 200);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create new transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createTransaction(Request $request, $id)
    {
        try {
            $order = ReservationPaymentMethod::where('group_id', $id)->firstOrFail();
            $status = '';
            $state = false;

            $receiverBank = '';
            $receiverAccount = '';
            $receiverName = '';
            $amount = 0;

            if ($order->paid == 1) {
                if (!$order->trans_hotel_created) {
                    // Get hotel
                    $hotel = $order->hotel;
                    // Today
                    $today = Carbon::today();

                    // Get hotel contract
                    // $contract = $hotel->hotelContracts()
                    //     ->where('is_active', 1)
                    //     ->where('is_confirmed', 1)
                    //     ->first();
                    $currOrdersCount = 0;

                    // Get org name for transaction
                    $orgName = $hotel->name;
                    if (str_contains($hotel->name, '|')) {
                        $orgName = explode(' |', $hotel->name)[0];
                    }

                    // Get hotel bank default bank code
                    $defaultBank = $hotel->hotelBanks()->where('is_default', 1)->first();

                    if (is_null($defaultBank)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Default банк тохируулаагүй байна.',
                        ], 400);
                    }

                    // Calculate if commission limit is active and its fully filled
                    $firstOfMonth = Carbon::today()->startOfMonth();

                    // $commission = is_null($contract) ? 0 : $contract->commission;
                    $commission = 0;

                    if ($hotel) {
                        $currOrdersCount = $hotel->reservations()
                            ->where('status', 'checked-out')
                            ->whereDate('created_at', '>=', $firstOfMonth)
                            ->whereDate('created_at', '<=', $today)
                            ->count();
                        $commission = $currOrdersCount > 5 ? 0 : $commission;
                    }

                    // $contractNo = is_null($contract) ? null : $contract->contract_no;
                    $receiverBank = is_null($defaultBank) ? null : $defaultBank->bank->code; //$defaultBank->bank->code;
                    $receiverAccount = is_null($defaultBank) ? null : $defaultBank->number;
                    $receiverName = is_null($defaultBank) ? null : $defaultBank->account_name;
                    $currency = is_null($defaultBank) ? 'MNT' : $defaultBank->currency;
                    $currencyRate = 1; // $currency === 'MNT' ? 1 : $order->dollar_rate;

                    $amount = $order->amount;
                    // if ($order->price_dollar != null && $currency !== 'MNT') {
                    //     $amount = $order->price_dollar - 1;
                    // } else {
                    //     $amount = $order->price;
                    // }

                    // Check commission for order request
                    // if ($order->is_order_request) {
                    //     $commission = 1;
                    // }

                    // Check created at for calculate commission
                    if ($order->created_at < '2022-01-01 00:00:00') {
                        $commission = 0;
                    } else {
                        $commission = 1;
                    }

                    // Set contract null until 2021-12-31
                    $contractNo = NULL;

                    // Calc hotel order amount
                    $amount = $amount / 100 * (100 - $commission);
                    // dd($amount);

                    // Pay at hotel үед гүйлгээний бүртгэл үүсгэхгүй
                    // Create new transaction
                    $http = new \GuzzleHttp\Client;

                    $transResponse = $http->post(config('services.trans_base_url') . '/t', [
                        'headers' => [
                            'Content-Type' => 'application/x-www-form-urlencoded',
                            'Origin' => 'api.myhotel.mn',
                            'x-and-auth-token' => $order->token,
                        ],
                        'form_params' => [
                            'contractNo' => $contractNo,
                            'orgName' => $orgName,
                            'objectId' => $order->id,
                            'receiverBank' => $receiverBank,
                            'receiverAccount' => $receiverAccount,
                            'receiverName' => $receiverName,
                            'description' => $order->number . ' төлбөр Айхотел',
                            'isAffiliate' => false,
                            'currency' => $currency,
                            'currencyRate' => $currencyRate,
                            'commission' => $commission,
                            'amount' => $amount,
                        ],
                    ]);

                    $response = json_decode((string) $transResponse->getBody(), true);
                    $isSuccess = $response['success'];
                    $order->trans_hotel_created = $isSuccess;
                    if ($isSuccess) {
                        $order->transaction_id = $response['id'];
                    }
                    $order->save();
                    $state = $isSuccess;

                    // Transaction status
                    $status = $isSuccess ? 'Захиалгын гүйлгээ амжилттай бүртгэгдлээ.' : 'Гүйлгээний бүртгэл үүсгэхэд алдаа гарлаа.';
                } else {
                    $status = 'Захиалгын гүйлгээ аль хэдийн үүссэн байна.';
                }
            } else if ($order->paid != 1) {
                $status = 'Захиалга баталгаажаагүй байна.';
            }
            return response()->json([
                'info' => [
                    'bank' => $receiverAccount,
                    'number' => $receiverAccount,
                    'name' => $receiverName,
                    'amount' => $amount
                ],
                'success' => $state,
                'message' => $status,
            ], 200);
        } catch (\GuzzleHttp\Exception\RequestException $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Хүсэлт гаргахад алдаа гарлаа. ' . $ex->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Алдаа гарлаа. ' . $e->getMessage(),
            ], 400);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            $phone = $request->input('phone');
            $code = $request->input('code');

            $msg = 'Your confirmation code: ' . $code;

            if ($phone && $code) {
                $http = new \GuzzleHttp\Client;
                $myResponse = $http->get('http://27.123.214.168/smsmt/mt?servicename=i&username=trip&from=149090&to=' . $phone . '&msg=' . $msg);

                $http2 = new \GuzzleHttp\Client;
                $myResponse = $http2->get('http://sms.unitel.mn/sendSMS.php?uname=itrip&upass=ddYFl3gpTk&sms=' . $msg . '&from=149090&mobile=' . $phone);

                $http3 = new \GuzzleHttp\Client;
                $myResponse = $http3->get('http://smsgw.skytel.mn:80/SMSGW-war/pushsms?id=1000282&src=149090&dest=' . $phone . '&text=' . $msg);

                $http4 = new \GuzzleHttp\Client;
                $myResponse = $http4->get('http://sms-special.gmobile.mn/cgi-bin/sendsms?username=ai_trip&password=trip*149&from=149090&to' . $phone . '&text=' . $msg);

                return response()->json([
                    'success' => true,
                    'message' => 'Амжилттай илгээгдлээ.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Хүсэлт алдаатай байна. Мессэж илгээх дугаар болон мессэж шалгана уу!'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'send Message Request error: ' . $e->getMessage(),
                'message' => 'Хүсэлт алдаатай байна. Мессэж илгээх дугаар болон мессэж шалгана уу!'
            ], 400);
        }
    }

    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}