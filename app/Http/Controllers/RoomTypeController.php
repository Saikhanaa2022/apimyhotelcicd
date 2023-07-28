<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Events\RoomTypeUpdated;

class RoomTypeController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\RoomType';
    protected $request = 'App\Http\Requests\RoomTypeRequest';

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->only([
            'id', 'name', 'defaultPrice', 'window', 'priceDayUse', 'priceTime', 'priceTimeCount', 'occupancy', 'hasTime',
            'occupancyChildren', 'hasExtraBed', 'extraBeds', 'floorSize',
            'description', 'bedTypeId', 'discountPercent', 'isResRequest',
            'byPerson', 'saleQuantity'
        ]);

        return array_merge($data, [
            'shortName' => strtoupper($request->input('shortName')),
            'hotelId' => $request->hotel->id,
        ]);
    }

    /**
     * Store or update the resource in storage.
     *
     * @param  array  $array
     * @return $data
     */
    protected function storeOrUpdate(array $array)
    {
        $params = snakeCaseKeys($array);
        $isSync = false;

        if (array_key_exists('id', $params) && $params['id']) {
            // Declare new model
            $model = new $this->model();

            // Find model
            $data = $this->newQuery()
                ->where($model->getTable() . '.id', $params['id'])
                ->firstOrFail();

            // Check default price change
            if ($data->default_price != $params['default_price']) {
                $isSync = true;
            }

            $data->update($params);

            // Check is sync
            if ($isSync) {
                event(new RoomTypeUpdated($data));
            }

        } else {
            $data = $this->model::create($params);
            $data->refresh();
        }

        return $data;
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
        if ($request->filled('names')) {
            foreach ($request->input('names') as $name) {
                \App\Models\Room::create([
                    'name' => $name,
                    'room_type_id' => $model->id,
                ]);
            }
        }

        if (!$request->input('id')) {
            // Create sample ratePlan
            $ratePlan = \App\Models\RatePlan::create([
                'name' => 'Үндсэн үнэ',
                'is_daily' => 1,
                'room_type_id' => $model->id
            ]);

            // Get rate plan of room type occupancy
            $occupancy = $model->occupancy;

            for($i = $occupancy; $i > 0; $i--) {
                // Creating occupancy rate plan for the rate plan
                \App\Models\OccupancyRatePlan::create([
                    'occupancy' => $i,
                    'discount_type' => 'currency',
                    'discount' => 0,
                    'is_default' => $i == $occupancy ? true : false,
                    'is_active' => false,
                    'rate_plan_id' => $ratePlan->id,
                ]);
            }
        } else {
            // Get rate plan of room type occupancy
            $occupancy = $model->occupancy;
            $tempArr = [];

            foreach($model->ratePlans as $ratePlan) {
                if ($model->occupancy != $ratePlan->occupancyRatePlans()->count()) {
                    // Temporary
                    $tempArr = $ratePlan->occupancyRatePlans()->get();
                    $ratePlan->occupancyRatePlans()->delete();

                    for($i = $occupancy - 1; $i > -1; $i--) {
                        // Creating occupancy rate plan for the rate plan
                        \App\Models\OccupancyRatePlan::create([
                            'occupancy' => $i + 1,
                            'discount_type' => 'currency',
                            'discount' => isset($tempArr[$i]['discount']) ? $tempArr[$i]['discount'] : 0,
                            'is_default' => ($i + 1) == $occupancy ? true : false,
                            'is_active' => isset($tempArr[$i]['is_active']) ? $tempArr[$i]['is_active'] : false,
                            'rate_plan_id' => $ratePlan->id,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Өрөөний төрөл солиход боломжтойг хайх
     */
    public function checkAvailability(Request $request)
    {
        // Validation
        $request->validate([
            'id' => 'required|integer',
        ]);

        $id = $request->input('id');
        $reservation = \App\Models\Reservation::find($id);

        if (is_null($reservation)) {
            return response()->json([
                'message' => 'Something went wrong. Try again.'
            ], 400);
        }

        $checkIn = $reservation->check_in;
        $checkOut = $reservation->check_out;
        $occupancy = $reservation->number_of_guests;

        // Check room types
        $roomTypes = $this->newQuery()
            ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                $query->unassigned($checkIn, $checkOut);
            }])->where('occupancy', '>=', $occupancy);

        // If reservation is not time then check ratePlans
        if (!$reservation->is_time) {
            $roomTypes = $roomTypes->with('ratePlans')->where('default_price', '>', 0);
        } else {
            $roomTypes = $roomTypes->where('has_time', 1);
        }

        $roomTypes = $roomTypes->havingRaw('rooms_count > 0')->get();
        foreach($roomTypes as $roomType) {
            $nights = stayNights($checkIn, $checkOut, false);
            foreach($roomType->ratePlans as $ratePlan) {
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
                $ratePlan->default_price = $defaultPrice;
            }
        }

        return response()->json([
            'roomTypes' => $roomTypes,
        ]);
    }


    /**
     * Сул өрөөтэй өрөөний төрлүүд хайх.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
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
            return response()->json([
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

        // Find requested partner
        // $partner = $request->hotel
        //     ->partners()
        //     ->where('partners.id', $request->input('partner.id'))
        //     ->first();

        // Check room types
        $roomTypes = $this->newQuery()
            ->select(['id', 'name', 'occupancy', 'occupancy_children', 'price_day_use', 'default_price'])
            ->with(['rooms' => function ($query) use ($checkIn, $checkOut, $roomId) {
                $query->select(['id', 'name', 'status', 'description', 'room_type_id'])
                    ->unassigned($checkIn, $checkOut)
                    ->when($roomId, function($query) use ($roomId) {
                        $query->where('rooms.id', $roomId);
                    });
            }]);

        // If reservation is not time then check ratePlans
        if ($stayType === 'night') {
            $roomTypes = $roomTypes->withAndwhereHas('ratePlans', function ($query) use ($checkIn, $checkOut) {//, $partner
                // $query->availableIn($reqCheckIn, $reqCheckOut)
                // $query->when($partner, function ($query) use ($partner) {
                //     return $query->whereHas('partners', function ($query) use ($partner) {
                //         $query->where('partners.id', $partner->id);
                //     });
                // });
            })->where('default_price', '>', 0);
        } else {
            $roomTypes = $roomTypes->where('has_time', true);
        }

        $roomTypes = $roomTypes->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                $query->unassigned($checkIn, $checkOut);
            }])
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


        // $roomTypes = $this->newQuery()
        //     ->with(['rooms' => function ($query) use ($checkIn, $checkOut, $roomId) {
        //         $query->select(['id', 'name', 'status', 'room_type_id'])
        //             ->unassigned($checkIn, $checkOut)
        //             ->when($roomId, function($query) use ($roomId) {
        //                 $query->where('rooms.id', $roomId);
        //             });
        //     }])
        //     ->withAndwhereHas('ratePlans', function ($query) use ($checkIn, $checkOut, $partner) {
        //         $query->availableIn($checkIn, $checkOut)
        //             ->when($partner, function ($query) use ($partner) {
        //                 return $query->whereHas('partners', function ($query) use ($partner) {
        //                     $query->where('partners.id', $partner->id);
        //                 });
        //             });
        //     })
        //     ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
        //         // $query->availableIn($checkIn, $checkOut);
        //         $query->unassigned($checkIn, $checkOut);
        //     }])
        //     ->when($roomTypeId, function ($query) use ($roomTypeId) {
        //         return $query->where('id', $roomTypeId);
        //     })
        //     // ->havingRaw('rooms_count > room_type_clones_count')
        //     // ->withCount(['roomTypeClones' => function ($query) use ($checkIn, $checkOut) {
        //     //     $query->whereHas('reservation', function ($query) use ($checkIn, $checkOut) {
        //     //         $query->where([
        //     //             ['check_in', '<', $checkOut],
        //     //             ['check_out', '>', $checkIn],
        //     //         ])->whereIn('status', [
        //     //             'pending', 'confirmed', 'no-show', 'checked-in'
        //     //         ]);
        //     //     });
        //     // }])
        //     ->havingRaw('rooms_count > 0')
        //     ->get();
        return response()->json([
            'roomTypes' => $roomTypes,
        ]);
    }

    /**
     * Update images.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveImages(Request $request, $id)
    {
        // Validate request
        $this->validator($request->all(), 'saveImagesRules');

        // Find room type
        $roomType = \App\Models\RoomType::findOrFail($id);

        $roomType->update([
            'images' => $request->input('images'),
        ]);

        return response()->json([
			'success' => 'true',
		]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Declare new model
        $model = new $this->model();

        // Check rooms of room types is assigned to reservations
        $exists = $model::where('id', $id)
            ->whereDoesntHave('roomTypeClones', function ($query) {
                $query->whereHas('reservation', function ($query) {
                    $query->whereIn('status', [
                        'pending', 'confirmed', 'no-show', 'checked-in'
                    ]);
                });
            })
            ->exists();

       

        $this->newQuery()
            ->where($model->getTable() . '.id', $id)
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Return amenities of selected room type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAmenities(Request $request, $id)
    {
        // Find room type
        $roomType = $this->model::findOrFail($id);

        $amenities = $roomType
            ->amenities()
            ->get();

        return response()->json([
            'amenities' => $amenities,
        ]);
    }

    /**
     * Sync Amenities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncAmenities(Request $request, $id)
    {
         // Validate request
        $this->validator($request->all(), 'syncAmenitiesRules');

        $ids = collect($request->input('amenities'))
            ->pluck('id');

        // Find room type
        $roomType = $this->model::findOrFail($id);

        $roomType
            ->amenities()
            ->sync($ids);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Calculate rates.
     *
     * @param  Array  $dayRates
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
}
