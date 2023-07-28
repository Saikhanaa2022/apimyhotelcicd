<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use App\Events\HotelCreated;
use App\Models\{
    CancellationPolicy,
    Country,
    Currency,
    OccupancyRatePlan,
    HotelSetting,
    PaymentMethod,
    Permission,
    Province,
    ProductCategory,
    Role,
    RoomType,
    Room,
    RatePlan,
    Service,
    ServiceCategory,
    Source,
    Tax,
    HotelType,
    District,
    CommonLocation,
    XRoomRoomTypes,
    SourceRoomTypes
};
use App\Traits\{BelongsToAdmin, TripTrait};

class HotelController extends BaseController
{
    use BelongsToAdmin;
    use TripTrait;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Hotel';
    protected $request = 'App\Http\Requests\HotelRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::query();
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->all();

        return $data;
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

        // MySQL transaction
        DB::beginTransaction();

        try {
            $hotel = $this->storeOrUpdate($this->requestParams($request));
            $hasIhotel = isset($hotel->ihotel) ? true : false;

            $places = $request->get('commonLocationIds');
            if (is_null($places)) {
                $hotel->commonLocations()->detach();
            } else {
                $places = explode(',', $places);
                $hotel->commonLocations()->sync($places);
            }

            if (!$request->filled('id')) {
                // Generate slug key
                $hotel->slug = substr(md5(uniqid(mt_rand())), 0, 6);
                $hotel->is_auto_arrange = 1;
                $hotel->save();

                $user = $request->user();
                $hotel->users()->sync($user->id);
                // $user->hotels()->sync($hotel->id);

                $is_vatpayer = $hotel->is_vatpayer;
                $is_citypayer = $hotel->is_citypayer;

                // Create default user role
                $role = Role::create([
                    'name' => 'Админ',
                    'is_default' => true,
                ]);

                $role->hotel()->associate($hotel);
                $role->save();

                // Syncing default user permission
                $permissionIds = Permission::select('id')->pluck('id');
                $role->permissions()->sync($permissionIds);

                // Create default source
                $source = Source::create([
                    'name' => 'Ресепшн',
                    'short_name' => 'РЕ',
                    'color' => '#04639B',
                    'hotel_id' => $hotel->id,
                    'is_default' => true,
                ]);

                // Create default payment method
                $paymentMethod = PaymentMethod::create([
                    'name' => 'Бэлэн мөнгө',
                    'color' => '#04639B',
                    'hotel_id' => $hotel->id,
                    'is_default' => true,
                    'is_paid' => true,
                ]);

                // Create default currency
                $currency = Currency::create([
                    'name' => 'Төгрөг',
                    'short_name' => 'MNT',
                    'rate' => 1,
                    'hotel_id' => $hotel->id,
                    'is_default' => true,
                ]);

                // Create default hotel setting
                HotelSetting::create([
                    'hotel_id' => $hotel->id,
                    'is_nightaudit_auto' => true,
                ]);

                // Create default taxes
                // Vat tax
                Tax::create([
                    'name' => 'НӨАТ',
                    'percentage' => 10,
                    'inclusive' => true,
                    'key' => 'vat',
                    'is_default' => true,
                    'is_enabled' => $is_vatpayer,
                    'hotel_id' => $hotel->id,
                ]);

                // City tax
                Tax::create([
                    'name' => 'НХАТ',
                    'percentage' => 1,
                    'inclusive' => true,
                    'key' => 'city',
                    'is_default' => true,
                    'is_enabled' => $is_citypayer,
                    'hotel_id' => $hotel->id,
                ]);

                // Service charge
                // Tax::create([
                //     'name' => 'Үйлчилгээний нэмэгдэл',
                //     'percentage' => 5,
                //     'inclusive' => false,
                //     'key' => null,
                //     'is_default' => false,
                //     'is_enabled' => false,
                //     'hotel_id' => $hotel->id,
                // ]);

                // Cancellation policy create
                CancellationPolicy::create([
                    'is_free' => true,
                    'has_prepayment' => false,
                    'cancellation_time_id' => 3,
                    'cancellation_percent_id' => 1,
                    'addition_percent_id' => 1,
                    'hotel_id' => $hotel->id,
                ]);

                // $user->hotel()->associate($hotel);
                $user->role()->associate($role);
                $user->save();

                // Create sample room type
                $roomType = RoomType::create([
                    'name' => 'Standard twin',
                    'short_name' => 'ST',
                    'occupancy' => 2,
                    'hotel_id' => $hotel->id,
                ]);

                // Create sample rooms
                Room::insert([
                    [
                        'name' => '201',
                        'status' => 'clean',
                        'room_type_id' => $roomType->id
                    ],
                    [
                        'name' => '202',
                        'status' => 'clean',
                        'room_type_id' => $roomType->id
                    ]
                ]);

                // Create sample ratePlan
                $ratePlan = RatePlan::create([
                    'name' => 'Үндсэн үнэ',
                    'is_daily' => 1,
                    'room_type_id' => $roomType->id
                ]);

                // Create sample occupancy rate plan
                OccupancyRatePlan::insert([
                    [
                        'occupancy' => 2,
                        'discount_type' => 'currency',
                        'discount' => 0,
                        'is_active' => 0,
                        'is_default' => 1,
                        'rate_plan_id' => $ratePlan->id
                    ],
                    [
                        'occupancy' => 1,
                        'discount_type' => 'currency',
                        'discount' => 0,
                        'is_active' => 0,
                        'is_default' => 0,
                        'rate_plan_id' => $ratePlan->id
                    ]
                ]);

                // Create sample service category
                $serviceCategory = ServiceCategory::create([
                    'name' => 'Ресторан',
                    'hotel_id' => $hotel->id
                ]);

                // Find product category
                $productCategory = ProductCategory::where('code', 2117600)->first();

                if ($productCategory) {
                    // Create sample service
                    Service::create([
                        'name' => 'Өглөөний цай',
                        'price' => 9999,
                        'countable' => 0,
                        'product_category_id' => $productCategory->id,
                        'service_category_id' => $serviceCategory->id
                    ]);
                }

                if (config('app.env') === 'production') {
                    event(new HotelCreated($hotel));
                }
            }
            // Commit transaction
            DB::commit();

            return $this->responseJSON($hotel);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update the age of child hotel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAge(Request $request)
    {
        try {
            // Find hotel
            $hotel = $request->hotel;
            if ($request->filled('age')) {
                $age = $request->input('age');
                // Change state
                $hotel->age_child = $age;
                $hotel->update();

                return response()->json([
                    'message' => 'success',
                ], 200);
            }
            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update the state of resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeState(Request $request, $id)
    {
        try {
            // Find hotel
            $hotel = $this->model::find($id);

            if ($request->has('isActive')) {
                $isActive = $request->input('isActive');

                // Change state
                $hotel->is_active = $isActive;
                $hotel->working_date = \Carbon\Carbon::today();
                $hotel->update();

                // Check is send email
                if ($request->has('isSendEmail') && $request->input('isSendEmail')) {
                    event(new \App\Events\HotelActivated($hotel, $isActive));
                }
            }

            return $this->responseJSON($hotel);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Return hotels of current user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByUser(Request $request)
    {
        // Get user hotels
        $hotels = $request->user()->hotels();

        // Check custom query
        if ($request->has('isActive')) {
            $hotels = $hotels->where('is_active', (int) $request->input('isActive'));
        }

        $hotels = $hotels->orderBy('created_at', 'desc')->get();

        if ($request->has('with')) {
            $hotels->load($request->input('with'));
        }

        // if ($request->user()->role === 'admin') {
        //     $hotels = $this->model::all();
        // }

        return response()->json([
            'hotels' => $hotels,
        ]);
    }

    /**
     * Update thumbnail image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveThumbnail(Request $request)
    {
        // Validate request
        // $this->validator($request->all(), 'saveImagesRules');

        $request->hotel->update([
            'thumbnail' => $request->input('thumbnail'),
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update images.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveImages(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'saveImagesRules');
        $images = $request->input('images');

        $request->hotel->update([
            'images' => $images,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Sync facilities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncFacilities(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'syncFacilitiesRules');

        $ids = collect($request->input('facilities'))
            ->pluck('id');

        $request->hotel
            ->facilities()
            ->sync($ids);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Sync hotel to wuBook.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncHotelWuBook(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'syncWuBookRules');

        try {
            // Find hotel
            $hotel = request()->hotel;
            $hotel = $this->storeOrUpdate($this->requestParams($request));

            // Get country and province
            $country = Country::find($request->countryId);
            $province = Province::find($request->provinceId);

            $http = new Client;
            $response = $http->post(config('lambda.baseUrl') . '/createProperty', [
                'json' => [
                    'lodg' => [
                        'name' => $request->name,
                        'address' => $request->address,
                        'url' => $request->website,
                        'zip' => $request->zipCode,
                        'city' => $province->international,
                        'phone' => '+976' . $request->phone,
                        'contact_email' => $request->email,
                        'booking_email' => $request->resEmail,
                        'country' => mb_strtoupper($country->locale, "UTF-8"),
                    ],
                    // 'account' => [
                    //     'first_name' => 'Test',
                    //     'last_name' => 'CTO',
                    //     'phone' => '999',
                    //     'lang' => 'EN',
                    //     'email' => 'test@gmail.com',
                    //     'currency' => 'USD'
                    // ]
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    // 'x-api-key' => 'C6opqDG0oi7entlZUPm5Q2hXVhkuB1so2KyqgtQ9',
                    'x-slug' => $hotel->id,
                    'x-lcode' => $hotel->wubook_lcode,
                ]
            ]);

            $res = json_decode((string) $response->getBody(), true);

            if ($res['status'] == 0) {
                $hotel->wubook_lcode = $res['response']['lcode'];
                $hotel->save();
            }

            return response()->json([
                'status' => $res['status'],
            ]);
        } catch (RequestException $e) {
            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Fetch connection data from ihotel.mn.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchData(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'fetchHotelRules');

        try {
            $ihotelId = $request->input('ihotelId');
            // Find hotel
            $hotel = request()->hotel;
            // $hotel->sync_id = $ihotelId;
            // $hotel->update();

            $http = new Client;
            $response = $http->post(config('services.ihotel.baseUrl') . '/fetch/roomTypes', [
                'json' => [
                    'id' => $ihotelId,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $res = json_decode((string) $response->getBody(), true);

            if ($res['status'] == false) {
                return response()->json([
                    'status' => false,
                    'message' => 'Алдаа гарлаа. Мэдээлэл татах боломжгүй байна.',
                ], 400);
            }

            return response()->json([
                'status' => true,
                'message' => '',
                'roomTypes' => $res['roomTypes'],
            ]);
        } catch (RequestException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Sync hotel to ihotel.mn.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncHotel(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'syncHotelRules');

        try {
            // Request data
            $data = [];

            // Find hotel
            $hotel = request()->hotel;
            $hotelSetting = $hotel->hotelSetting;

            // Update hotel
            $hotel->sync_id = $request->input('ihotelId');
            $hotel->has_ihotel = $request->input('hasIhotel');
            $hotel->update();

            // Update hotel setting
            $hotelSetting->has_res_request = $request->input('hasResRequest');
            $hotelSetting->update();

            // Check if source already created
            $checkSource = $hotel->sources()
                ->where('service_name', 'ihotel')
                ->first();

            // Create or update ihotel source 
            if ($hotel->has_ihotel) {
                if ($checkSource === NULL) {
                    // Create default source
                    Source::create([
                        'name' => 'iHotel.mn',
                        'short_name' => 'IH',
                        'color' => '#04639B',
                        'hotel_id' => $hotel->id,
                        'is_default' => true,
                        'is_active' => true,
                        'service_name' => 'ihotel',
                    ]);
                } else {
                    $checkSource->is_active = true;
                    $checkSource->update();
                }
            } else {
                if ($checkSource !== NULL) {
                    $checkSource->is_active = false;
                    $checkSource->update();
                }
            }

            // Build roomTypes for notify ihotel
            $roomTypes = $request->input('roomTypes');

            foreach ($roomTypes as $item) {
                $roomType = $hotel->roomTypes()->where('id', $item['id'])->first();
                if (!is_null($roomType)) {
                    $roomSyncId = array_key_exists('syncId', $item) ? $item['syncId'] : null;
                    $roomType->sync_id = $roomSyncId;
                    $roomType->is_res_request = $item['isResRequest'];
                    $roomType->by_person = $item['byPerson'];
                    $roomType->start_date = $item['startDate'];
                    $roomType->end_date = $item['endDate'];
                    $roomType->days = $item['days'];
                    if ($item['isResRequest'] == true) {
                        $roomType->discount_percent = is_array($item['discountPercent'])
                            ? $item['discountPercent']
                            : explode(',', $item['discountPercent']);

                        $roomType->sale_quantity = $item['saleQuantity'];
                    } else {
                        $roomType->discount_percent = NULL;
                        $roomType->sale_quantity = NULL;
                    }

                    $roomType->update();

                    $inclusionNotes = array_key_exists('inclusionNotes', $item) ? $item['inclusionNotes'] : NULL;

                    // if (!is_null($roomSyncId)) {
                    array_push($data, [
                        'id' => $roomSyncId,
                        'syncId' => $roomType->id,
                        'isResRequest' => $roomType->is_res_request,
                        'discountPercent' => $roomType->discount_percent,
                        'saleQuantity' => $roomType->sale_quantity,
                        'inclusionNotes' => $inclusionNotes,
                        'byPerson' => $roomType->by_person,
                        'startDate' => $roomType->start_date,
                        'endDate' => $roomType->end_date,
                        'days' => $roomType->days
                    ]);
                    // }
                }
            }

            if (count($data) > 0) {
                // Send request to ihotel
                $http = new Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/sync/roomTypes', [
                    'json' => [
                        'id' => $hotel->sync_id,
                        'myHotelId' => $hotel->id,
                        'roomTypes' => $data,
                        'isActivate' => $hotel->has_ihotel,
                        'hasResRequest' => $hotelSetting->has_res_request
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $res = json_decode((string) $response->getBody(), true);

                // Get status
                if ($res['status'] == false) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
                    ], 400);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай хадгалагдлаа.',
            ]);
        } catch (RequestException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update hotel online book service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOnlineBook(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'onlineBookRules');

        try {
            // Find hotel
            $hotel = request()->hotel;
            $hotel->has_online_book = $request->input('hasOnlineBook');
            $hotel->update();

            // Check if source already created
            $checkSource = $hotel->sources()
                ->where('service_name', 'onlineBook')
                ->first();

            if ($hotel->has_online_book) {
                if ($checkSource === NULL) {
                    // Create default source
                    Source::create([
                        'name' => 'Онлайн захиалга',
                        'short_name' => 'ОЗ',
                        'color' => '#04639B',
                        'hotel_id' => $hotel->id,
                        'is_default' => true,
                        'is_active' => true,
                        'service_name' => 'onlineBook',
                    ]);
                } else {
                    $checkSource->is_active = true;
                    $checkSource->update();
                }
            } else {
                if ($checkSource !== NULL) {
                    $checkSource->is_active = false;
                    $checkSource->update();
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай хадгалагдлаа.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Update hotel chatbot service.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateChatbot(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'chatbotRules');

        try {
            // Find hotel
            $hotel = request()->hotel;
            $hotel->has_chatbot = $request->input('hasChatbot');
            $hotel->update();

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай хадгалагдлаа.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Get hotel edit data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEditData(Request $request)
    {
        try {
            $hotel = $request->hotel;
            $hotelTypes = HotelType::all();
            $countries = Country::all();
            $provinces = Province::all();
            $districts = District::all();
            $common_locations = CommonLocation::all();

            $hotel->load('hotelType');

            $district = $province = $country = null;

            if ($hotel->district_id) {
                $district = $hotel->district;
                $province = $district->province;
                $country = $province->country;
            }

            $hotel->district_id = is_null($district) ? null : $district->id;
            $hotel->province_id = is_null($province) ? null : $province->id;
            $hotel->country_id = is_null($country) ? null : $country->id;

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай.',
                'hotel' => $hotel,
                'hotelTypes' => $hotelTypes,
                'countries' => $countries,
                'provinces' => $provinces,
                'districts' => $districts,
                'commonLocations' => $common_locations
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Get hotel edit data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getXRoomRoomTypes(Request $request)
    {
        try {
            $hotel = $request->hotel;
            $roomTypes = RoomType::with('rooms')->where('hotel_id', $hotel->id)->get();

            foreach ($roomTypes as $item) {
                $room = XRoomRoomTypes::where('room_type_id', $item->id)->first();
                if ($room) {
                    $item->hasXroom = true;
                } else {
                    $item->hasXroom = false;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай.',
                'roomTypes' => $roomTypes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.'
            ], 400);
        }
    }

    /**
     * Connect hotel to xroom.mn
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncXRoom(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'syncXRoomRules');

        try {
            // MySQL transaction
            DB::beginTransaction();
            // Request data
            $data = [];

            // Find hotel
            $hotel = request()->hotel;
            $hotelSetting = $hotel->hotelSetting;

            // Update hotel
            $hotel->has_xroom = $request->input('hasXroom');
            $hotel->update();

            // Check if source already created
            $checkSource = $hotel->sources()
                ->where('service_name', 'xroom')
                ->first();

            // Create or update ihotel source 
            if ($hotel->has_xroom) {
                if ($checkSource === NULL) {
                    // Create default source
                    Source::create([
                        'name' => 'xroom.mn',
                        'short_name' => 'XR',
                        'color' => '#F6063A',
                        'hotel_id' => $hotel->id,
                        'is_default' => true,
                        'is_active' => true,
                        'service_name' => 'xroom',
                    ]);
                } else {
                    $checkSource->is_active = true;
                    $checkSource->update();
                }
            } else {
                if ($checkSource !== NULL) {
                    $checkSource->is_active = false;
                    $checkSource->update();
                }
            }

            // Build roomTypes for notify ihotel
            $rooms = $request->input('rooms');

            $roomTypes = $request->input('roomTypes');

            $xroomTypes = $hotel->xroomTypes()->get()->map(function ($x) {
                $x->active = 0;
                return $x;
            });

            foreach ($rooms as $item) {
                $room = Room::where('id', $item['id'])->first();
                $room->has_xroom = $item['hasXroom'];
                $room->update();
            }

            foreach ($roomTypes as $roomType) {
                $xroomType = $xroomTypes->where('room_type_id', $roomType['id'])->first();
                if ($xroomType != null) {
                    if ($roomType['hasXroom'] == true) {
                        $xroomType->active = $roomType['hasXroom'];
                        $xroomType->update();
                    } else {
                        $xroomType->delete();
                    }
                } else if ($roomType['hasXroom'] == true) {
                    XRoomRoomTypes::create([
                        'hotel_id' => $hotel->id,
                        'room_type_id' => $roomType['id'],
                        'active' => 1,
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай хадгалагдлаа.',
            ], 200);
        } catch (RequestException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Connect hotel to anything source
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectSource(Request $request)
    {
        try {
            // Validate request
            $this->validator($request->all(), 'sourceRules');
            // MySQL transaction
            DB::beginTransaction();
            // Request data
            $data = [];

            // Find hotel
            $hotel = request()->hotel;
            $roomCount = count($hotel->rooms);

            // Check if source already created
            $checkSource = $hotel->sources()
                ->where('service_name', $request->service_name)
                ->first();

            if ($request->hasSource && $checkSource) {
                return response()->json([
                    'success' => false,
                    'message' => 'Холболт хийгдсэн байна.',
                ], 400);
            }

            $inActiveSource = Source::where([['service_name', $request->service_name], ['hotel_id', $hotel->id]])->first();

            if ($request->hasSource && $inActiveSource) {
                $inActiveSource->is_active = true;
                $inActiveSource->update();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Амжилттай хадгаллаа.',
                ], 200);
            }

            // Create or update ihotel source 
            if ($request->hasSource && $hotel->sync_id) {
                $hotel = $this->fetchHotel($hotel);
                if (is_null($hotel)) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Sync id not found.',
                        'message' => 'This hotel is not impossible to connect.',
                    ], 400);
                }

                $hotel->rooms_count = $roomCount;

                // return prepared hotel data
                $sendData = $this->hotelTrait($hotel);
                $http = new \GuzzleHttp\Client;
                $res = $http->post(config('services.ctrip.baseUrl'), [
                    'json' => $sendData,
                    'headers' => [
                        'Code' => 1591,
                        'Authorization' => '37245405ebfa7f118ed02f76bbd8ddb2fa3f77e0e1f9d61c2afdf057b29d7516',
                        'Content-Type' => 'application/json'
                    ]
                ]);

                $data = json_decode($res->getBody());

                $message = $data->message;

                if ($message !== "Success") {
                    $this->logResponse($data, true, 'error');
                    return response()->json([
                        'status' => false,
                        'message' => 'Алдаа гарлаа. [' . $message . ']',
                    ], 400);
                } else {
                    $this->logResponse($data, true);
                }

                if ($checkSource === NULL) {
                    // Create default source and delete old sources
                    $source = Source::where([['service_name', $request->service_name], ['hotel_id', $hotel->sync_id]])->each(function ($source, $key) {
                        $source->delete();
                    });

                    $checkSource = Source::updateOrCreate([
                        'name' => $request->service_name,
                        'short_name' => strtoupper(str_split($request->service_name, 2)[0]),
                        'hotel_id' => $hotel->sync_id,
                        'is_default' => true,
                        'is_active' => true,
                        'service_name' => $request->service_name,
                    ]);
                } else {
                    $checkSource->is_active = true;
                    $checkSource->update();
                }
            } else {
                if ($checkSource !== NULL) {
                    $checkSource->is_active = false;
                    $checkSource->update();
                }
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'status' => true,
                'ctrip' => $data,
                'message' => 'Амжилттай хадгаллаа.',
            ], 200);
        } catch (RequestException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }
    }

    /**
     * get source room types 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sourceRoomTypes(Request $request)
    {
        try {
            $hotel = $request->hotel;
            $roomTypes = RoomType::where('hotel_id', $hotel->id)->get();
            $source = $hotel->sources()
                ->where('service_name', $request->service_name)
                ->first();

            if ($source === NULL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Алдаа гарлаа. Суваг холбоогүй байна.',
                ], 400);
            }

            foreach ($roomTypes as $item) {
                $room = SourceRoomTypes::where([['room_type_id', $item->id], ['source_id', $source->id]])->first();
                if ($room) {
                    $item->hasSource = true;
                    $item->sale_quantity = $room->sale_quantity;
                } else {
                    $item->hasSource = false;
                    $item->sale_quantity = 0;
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Амжилттай.',
                'roomTypes' => $roomTypes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.'
            ], 400);
        }
    }

    /**
     * Connect room types with any source such as ctrip, xroom.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectRoomTypes(Request $request)
    {
        // Validate request
        // $this->validator($request->all(), 'syncXRoomRules');
        try {
            // MySQL transaction
            DB::beginTransaction();
            // Request data
            $data = [];
            $roomTypes = $request->input('roomTypes');
            $sendRoomTypes = [];
            $roomIds = [];
            $hasAll = $request->input('hasAll');

            // Find hotel
            $hotel = request()->hotel;

            // Check if source already created
            $checkSource = $hotel->sources()
                ->where('service_name', $request->service_name)
                ->first();

            if ($checkSource === NULL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Алдаа гарлаа. Суваг холбоогүй байна.',
                ], 400);
            }

            if ($hasAll) {
                $roomTypes = $hotel->roomTypes()->whereNotNull('sync_id')->get()->toArray();
            }
            // check roomTypes is already exists
            foreach ($roomTypes as $item) {
                $roomType = $hotel->roomTypes()->where('id', $item['id'])->whereNotNull('sync_id')->first();
                $sourceRoomType = $hotel->sourceRoomTypes()
                    ->where([['room_type_id', $item['id']], ['source_id', $checkSource->id]])
                    ->first();

                if (!is_null($roomType) && !is_null($sourceRoomType)) {
                    if ($item['hasSource'] == true) {
                        $sourceRoomType->active = 1;
                    } else {
                        $sourceRoomType->active = 0;
                    }
                    $sourceRoomType->update();
                } else if (!is_null($roomType) && is_null($sourceRoomType)) {
                    if ($hasAll) {
                        $roomIds[] = $roomType->id;
                    } else {
                        if ($item['hasSource'] == true) {
                            $roomIds[] = $roomType->id;
                        }
                    }
                }
            }

            $hotelResult = $this->fetchHotel($hotel);
            if (is_null($hotelResult)) {
                return response()->json([
                    'status' => false,
                    'error' => 'Sync id not found.',
                    'message' => 'This hotel is not impossible to connect.',
                ], 400);
            }

            $hotel['is_internet'] = $hotelResult->is_internet;

            $roomTypesMyHotel = $hotel->roomTypes()->with(['amenities'])->find($roomIds);
            $iHotelRooms = $this->fetchRoom($hotel); // iHotel rooms

            foreach ($iHotelRooms as $room) {
                foreach ($roomIds as $id) {
                    if ($room->sync_id === $id) {
                        $sendRoomTypes[] = $room;
                    }
                }
            }

            // concat myhotel roomtype and ihotel room data
            foreach ($roomTypesMyHotel as $r) {
                foreach ($sendRoomTypes as $r2) {
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

            $rate = $this->fetchCurrency('en-US', $hotel); // rate from ihotel
            $sendData = $this->room($hotel, $sendRoomTypes, $rate);
            $response = null;
            if (count($sendData['roomDatas']) > 0) {
                $http = new \GuzzleHttp\Client;
                $res = $http->post(config('services.ctrip.baseUrlRoom'), [
                    'json' => $sendData,
                    'headers' => [
                        'Code' => 1591,
                        'Authorization' => '37245405ebfa7f118ed02f76bbd8ddb2fa3f77e0e1f9d61c2afdf057b29d7516',
                        'Content-Type' => 'application/json'
                    ]
                ]);

                $response = json_decode($res->getBody());

                $message = $response->message;

                if ($message !== "Success") {
                    $this->logResponse($response, false, 'error');
                    return response()->json([
                        'status' => false,
                        'message' => 'Алдаа гарлаа. [' . $message . ']',
                    ], 400);
                } else {
                    $this->logResponse($response);
                }
            }

            // create source room types before not exists
            foreach ($roomTypesMyHotel as $item) {
                SourceRoomTypes::create([
                    'hotel_id' => $hotel->id,
                    'room_type_id' => $item->id,
                    'source_id' => $checkSource->id,
                    'active' => 1
                ]);
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
                'ctrip' => $response,
                'message' => 'Амжилттай хадгалагдлаа.',
            ], 200);
        } catch (RequestException $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'success' => false,
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
}