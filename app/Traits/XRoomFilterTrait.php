<?php

namespace App\Traits;

use App\ErrorLog;
use App\Jobs\SendEmail;
use App\Models\{Hotel, Reservation, Cancellation, Guest, GuestClone, SourceClone, TaxClone, UserClone, Room, RoomType};
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait XRoomFilterTrait
{

    function searchRooms(Carbon $date, $stayType, $lat = null, $lng = null)
    {
        $date_con = $date->subDay(1)->format('Y-m-d');
        $date_end = $date->subDay(1)->format('Y-m-d');

        if ($stayType == 'day') {
            $date_con = $date->copy()->startOfDay()->format('Y-m-d');
            $date_end = $date->copy()->startOfDay()->format('Y-m-d');
        }

        $reservations = DB::table('reservations')
            ->leftJoin('room_clones', 'reservations.room_clone_id', '=', 'room_clones.id')
            ->leftJoin('rooms', 'room_clones.room_id', '=', 'rooms.id')
            ->whereIn('reservations.status', ['confirmed', 'no-show','checked-in'])
            ->where(function ($query) use ($date_con, $date_end) {
                $query->where('reservations.check_in', '>=', $date_con)
                    ->where('reservations.check_out', '>=', $date_end);
            })
            ->where('stay_type', $stayType)
            ->select('rooms.room_type_id', DB::raw('sum(case when reservations.id is not null then 1 else 0 end) as reserved'))
            ->groupBy('rooms.room_type_id');
        // $blocks = DB::table('blocks')
        //     ->where('start_date', '<=', $date_con)
        //     ->where('end_date', '>=', $date_con)
        //     ->distinct()->pluck('blocks.room_id');

        \DB::enableQueryLog();
        $room_types_with_availablity = DB::table('rooms')
            ->select('rooms.room_type_id', DB::raw('count(*) as enabled_count'))
            // ->whereNotIn('rooms.id', $blocks)
            ->where('rooms.has_xroom', 1)
            ->groupBy('rooms.room_type_id')
            ->havingRaw('rooms.room_type_id is not null');

        $date_str = Carbon::now()->format('Y-m-d');
        $daily_rates = DB::table('daily_rates')
            ->leftJoin('rate_plans', 'rate_plan_id', '=', 'rate_plans.id')
            ->where('rate_plans.is_online_book', '1')
            ->where('daily_rates.date', $date_str)
            ->select('daily_rates.id', 'daily_rates.date', 'daily_rates.value', 'rate_plans.room_type_id');

        $select = 'room_types.id as room_type_id, 
            room_types.name as room_type_name,
            room_types.images,
            case when d.value is null then room_types.default_price else d.value end as default_price,
            room_types.price_day_use,
            room_types.occupancy,
            bed_types.id as bed_type_id,
            bed_types.name as bed_type_name,
            hotels.name as hotel_name,
            hotels.id as hotel_id,
            hotels.address as address,
            hotels.short_address,
            hotels.lat,
            hotels.lng,
            hotels.phone,
            r.enabled_count,
            xroom_room_types.manual_order,
            case when t.reserved is null then 0 else t.reserved end as reserved_count';
        $bindings = [];
        if (!empty($lat) && !empty($lng)) {
            $select = "$select,
            ST_Distance_Sphere(POINT(?, ?), POINT(hotels.lng, hotels.lat)) as distance_km";
            $bindings = [
                $lng,
                $lat
            ];
        }

        return DB::table('room_types')
            ->leftJoin('bed_types', 'room_types.bed_type_id', '=', 'bed_types.id')
            ->leftJoin('hotels', 'hotels.id', '=', 'room_types.hotel_id')
            ->joinSub($room_types_with_availablity, 'r', 'room_types.id', '=', 'r.room_type_id', 'inner')
            ->leftJoinSub($reservations, 't', function ($join) {
                $join->on('room_types.id', '=', 't.room_type_id');
            })
            ->leftJoin('xroom_room_types', function ($join) {
                $join->on('xroom_room_types.room_type_id', '=', 'room_types.id');
                $join->on('xroom_room_types.hotel_id', '=', 'hotels.id');
            })
            ->leftJoinSub($daily_rates, 'd', function ($join) {
                $join->on('room_types.id', '=', 'd.room_type_id');
            })
            ->selectRaw(
                $select,
                $bindings
            )
            ->where([['hotels.hotel_type_id', 5], ['hotels.has_xroom', 1]])
            ->where('hotels.is_active', 1)
            ->whereNotNull('xroom_room_types.id');
    }

    /**
     * @param \App\Models\CommonLocation location
     * @return Room
     */
    function searchRoomDefault($location)
    {
        $ids = $this->roomTypesId($location);

        return Room::whereIn('room_type_id', $ids)
            ->with([
                'roomType' => function ($query) {
                    $query->select([
                        'id',
                        'name',
                        'short_name',
                        'sync_id',
                        'hotel_id',
                        'bed_type_id',
                        'default_price',
                        'price_day_use',
                        'images',
                        'description',
                        'discount_percent',
                        'occupancy'
                    ])
                        ->with([
                            'bedType' => function ($query) {
                                $query->select(['id', 'name', 'name_en', 'bed_count']);
                            }
                        ])
                        ->with('xroomType')
                        ->with([
                            'hotel' => function ($query) {
                                $query->select([
                                    'id',
                                    'sync_id',
                                    'company_name',
                                    'name',
                                    'address',
                                    'phone',
                                    'res_phone',
                                    'email',
                                    'res_email',
                                    'image',
                                    'images',
                                    'hotel_type_id',
                                    'location',
                                    'common_location_ids',
                                    'has_xroom'
                                ])
                                    ->with(['hotelBanks']);
                            }
                        ]);
                }
            ]);
    }

    /**
     * @param \App\Models\CommonLocation location
     * @param string stayType
     * @return $roomQuery
     */
    function searchRoomDay($location, $stayType)
    {
        $ids = $this->roomTypesId($location);
        $roomQuery = null;

        if ($stayType === "day") {
            $roomQuery = Room::whereIn('room_type_id', $ids)
                ->where('is_day', true)
                ->where('is_night', false);
        } else {
            $roomQuery = Room::whereIn('room_type_id', $ids)
                ->where('is_night', true)
                ->where('is_day', false);
        }

        return $roomQuery->with([
            'roomType' => function ($query) {
                $query->select([
                    'id',
                    'sync_id',
                    'name',
                    'short_name',
                    'hotel_id',
                    'bed_type_id',
                    'default_price',
                    'price_day_use',
                    'images',
                    'description',
                    'discount_percent',
                    'occupancy'
                ])
                    ->with('xroomType')
                    ->with([
                        'bedType' => function ($query) {
                            $query->select(['id', 'name', 'name_en', 'bed_count']);
                        }
                    ])
                    ->with([
                        'hotel' => function ($query) {
                            $query->select([
                                'id',
                                'sync_id',
                                'company_name',
                                'name',
                                'address',
                                'phone',
                                'res_phone',
                                'email',
                                'res_email',
                                'image',
                                'images',
                                'hotel_type_id',
                                'location',
                                'common_location_ids',
                                'has_xroom'
                            ])
                                ->with(['hotelBanks']);
                        }
                    ]);
            }
        ]);
    }

    /**
     * @param \app\Models\CommonLocation location
     * @param string stayType
     * @param integer minPrice
     * @param integer maxPrice
     * @return $roomQuery
     */
    function searchRoomFull($location, $stayType, $minPrice, $maxPrice)
    {
        $isDay = $stayType == "day" ? true : false;
        $isNight = $stayType == "night" ? true : false;

        $ids = $this->roomTypesId($location, $minPrice, $maxPrice);

        return Room::whereIn('room_type_id', $ids)
            ->where(function ($query) use ($isDay, $isNight) {
                $query->where('is_day', $isDay)
                    ->where('is_night', $isNight);
            })
            ->with([
                'roomType' => function ($query) {
                    $query->select([
                        'id',
                        'sync_id',
                        'name',
                        'short_name',
                        'hotel_id',
                        'bed_type_id',
                        'default_price',
                        'price_day_use',
                        'images',
                        'description',
                        'discount_percent',
                        'occupancy'
                    ])
                        ->with([
                            'bedType' => function ($query) {
                                $query->select(['id', 'name', 'name_en', 'bed_count']);
                            }
                        ])
                        ->with('xroomType')
                        ->with([
                            'hotel' => function ($query) {
                                $query->select([
                                    'id',
                                    'sync_id',
                                    'company_name',
                                    'name',
                                    'address',
                                    'phone',
                                    'res_phone',
                                    'email',
                                    'res_email',
                                    'image',
                                    'images',
                                    'hotel_type_id',
                                    'location',
                                    'common_location_ids',
                                    'has_xroom'
                                ])
                                    ->with(['hotelBanks']);
                            }
                        ]);
                }
            ]);
    }

    /**
     * @param \app\Models\CommonLocation location
     * @param integer minPrice
     * @param integer maxPrice
     * @return $ids
     */
    function roomTypesId($locations, $minPrice = 0, $maxPrice = 0)
    {
        $roomTypes = RoomType::whereHas('xroomType', function ($query) {
            $query->where('active', 1);
        })->where(function ($query) use ($minPrice, $maxPrice) {
            if ($minPrice > 0 && $maxPrice > 0) {
                $query->whereBetween('default_price', [$minPrice, $maxPrice])
                    ->whereBetween('price_day_use', [$minPrice, $maxPrice]);
            } else {
                $query->where('default_price', '>', 0)
                    ->where('price_day_use', '>', 0);
            }
        })
            ->whereHas('hotel', function ($query) use ($locations) {
                $query->where([['hotel_type_id', 5], ['has_xroom', 1]]);
                if (!is_null($locations)) {
                    $query->whereIn('common_location_ids', $locations);
                }
            })->get();

        $ids = [];

        foreach ($roomTypes as $rt) {
            $ids[] = $rt->id;
        }

        return $ids;
    }

}