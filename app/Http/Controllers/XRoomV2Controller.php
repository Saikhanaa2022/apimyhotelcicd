<?php

namespace App\Http\Controllers;

use App\Models\{
    RoomType,
    CommonLocation,
    BedType,
    XRoomReservation
};
use App\Models\Hotel;
use App\Traits\{ReservationTrait, XRoomFilterTrait};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Image;

// define("ERRORMESSAGE", "Алдаа гарлаа. Та дахин оролдоно уу.");
// define("DATEMESSAGE", "Ирэх болон Гарах огноо буруу байгаа тул шалгана уу.");

class XRoomV2Controller extends Controller
{
    use ReservationTrait;
    use XRoomFilterTrait;
    /**
     * The model associated with the controller.
     *
     * @var string
     */
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
            // Validation
            $request->validate([
                'stayType' => 'required|in:night,day',
            ]);

            $rowsPerPage = (int) $request->query('rowsPerPage', 12);
            $page = (int) $request->query('page', 1);
            $sortBy = snake_case($request->query('sortBy', 'manual_order'));
            $direction = $request->query('direction', 'asc');
            $stayType = $request->input('stayType');
            $searchQuery = null;
            $minPrice = $request->input('min', 0);
            $maxPrice = $request->input('max', 0);
            $query = $request->input('query');
            $date = Carbon::now();

            $lat = (double) $request->query('lat');
            $lng = (double) $request->query('lng');

            if (!empty($lat) && !empty($lng)) {
                $searchQuery = $this->searchRooms($date, $stayType, $lat, $lng);
            } else {
                $searchQuery = $this->searchRooms($date, $stayType);
            }


            \DB::enableQueryLog();

            if (!empty($request->input('location'))) {
                $location = DB::table('hotel_location')
                    ->whereIn('common_location_id', $request->input('location'));

                if (!empty($query)) {
                    $location = $location
                        ->leftJoin('common_locations', 'hotel_location.common_location_id', '=', 'common_locations.id')
                        ->where('common_locations.name', 'like', '%' . $query . '%');
                }

                $location = $location
                    ->pluck(DB::raw('distinct hotel_id as hotel_id'));

                $searchQuery = $searchQuery->whereIn('hotels.id', $location);
            }

            if (!empty($query)) {
                $searchQuery = $searchQuery->where(function ($qr) use ($query) {
                    $searchParam = '%' . $query . '%';
                    $qr->where('hotels.name', 'like', $searchParam)
                        ->orWhere('room_types.name', 'like', $searchParam)
                        ->orWhere('bed_types.name', 'like', $searchParam)
                        ->orWhere('hotels.address', 'like', $searchParam)
                        ->orWhere('hotels.short_address', 'like', $searchParam);
                });
            }

            if (!empty($request->input('hotel'))) {
                $searchQuery = $searchQuery->whereIn('hotels.id', $request->input('hotel'));
            }

            if (!empty($request->input('bedtypes'))) {
                $searchQuery = $searchQuery->whereIn('room_types.bed_type_id', $request->input('bedtypes'));
            }

            if ($stayType == 'day') {
                $searchQuery = $searchQuery->where('room_types.price_day_use', '>', 0);
            }

            if (!empty($lat) && !empty($lng)) {
                $searchQuery = $searchQuery->having('distance_km', '<=', 1000);
            }

            if ($maxPrice > 0 && $minPrice > 0) {
                if ($stayType == 'day') {
                    $searchQuery = $searchQuery->whereBetween('room_types.price_day_use', [$minPrice, $maxPrice]);
                } else {
                    $searchQuery = $searchQuery->where(function ($query) use ($minPrice, $maxPrice) {
                        $query->whereBetween('default_price', [$minPrice, $maxPrice])
                            ->orWhereBetween('d.value', [$minPrice, $maxPrice]);
                    });
                }
            }

            $sortTable = 'room_types.';
            if ($sortBy == 'manual_order') {
                $sortTable = 'xroom_room_types.';
            }
            $searchQuery = $searchQuery->orderBy($sortTable . $sortBy, $direction)->orderBy('xroom_room_types.created_at', 'desc');


            if ($rowsPerPage === 0) {
                $hotelData = $searchQuery->get();
            }

            if ($rowsPerPage === -1) {
                $rowsPerPage = $searchQuery->count();
            }

            if (!empty($lat) && !empty($lng)) {
                $total = $searchQuery
                    ->get()->count();
            } else {
                $total = $searchQuery->count();
            }

            $expire_date = Carbon::now()->subMinutes(config('constants.date.expire'))->format('Y-m-d H:i');

            $pendingReservations = DB::table('xroom_reservations')
                ->where('stay_type', $stayType)
                ->where('payment_status', 'pending')
                ->where('created_at', '>', $expire_date) // older than 2 hour invoices are expired
                ->groupBy('room_type_id')
                ->selectRaw('room_type_id, count(id) as pending')
                ->get();

            $hotelData = $searchQuery->offset(($page - 1) * $rowsPerPage)
                ->limit($rowsPerPage)->get();
            
            \Log::info('count: '. $pendingReservations->count());
            foreach ($hotelData as $item) {
                $pend = $pendingReservations->first(function ($value, int $key) use ($item) {
                    return $value->room_type_id == $item->room_type_id;
                });

                if ($pend != null) {
                    $item->reserved_count = $item->reserved_count + $pend->pending;
                }
            }
            // dd(\DB::getQueryLog());

            return response()->json([
                'list' => $hotelData,
                'total' => $total,
                'rowsPerPage' => $rowsPerPage,
                'page' => $page,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => config('constants.log.error'),
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
            $stayType = $request->input('stay_type');
            $common_locations = CommonLocation::select(
                'id',
                'name',
                'name_en',
                'description',
                'district_id',
                'slug',
                'longitude_latitude'
            )
                ->whereHas('hotels', function ($query) use ($stayType) {
                    $query->where([['hotel_type_id', 5], ['has_xroom', 1]]);
                    $query->whereHas('roomTypes', function ($q) use ($stayType) {
                        if ($stayType == 'day') {
                            $q->whereNotNull('price_day_use');
                        }
                    });
                })
                ->get();

            return response()->json([
                'success' => true,
                'commonLocations' => $common_locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => config('constants.log.error'),
            ], 400);
        }
    }

    public function getAmenities(Request $request, $roomTypeId)
    {
        try {
            $roomType = RoomType::find($roomTypeId);

            if (is_null($roomType)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wrong room type'
                ], 400);
            }

            $result = DB::table('amenity_room_type')
                ->leftJoin('amenities', 'amenity_room_type.amenity_id', '=', 'amenities.id')
                ->where('room_type_id', $roomTypeId)
                ->select(
                    'amenity_room_type.id',
                    'amenity_room_type.amenity_id',
                    'amenities.name'
                )->get();

            return response()->json([
                'success' => true,
                'amenities' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => config('constants.log.error'),
            ], 400);
        }
    }

    public function getBedTypes(Request $request)
    {
        try {
            $stayType = $request->input('stay_type');

            $query = DB::table('room_types')
                ->join('xroom_room_types', function ($query) {
                    $query->on('xroom_room_types.room_type_id', '=', 'room_types.id');
                    $query->on('xroom_room_types.hotel_id', '=', 'room_types.hotel_id');
                });

            if ($stayType == 'day') {
                $query = $query->whereNotNull('price_day_use');
            }

            $xroomBedTypes = $query
                ->pluck('room_types.bed_type_id');

            $bedTypes = BedType::whereIn('id', $xroomBedTypes)->get();

            return response()->json([
                'success' => true,
                'data' => $bedTypes
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'error' => '' . $ex->getMessage(),
                'message' => config('constants.log.error'),
            ], 400);
        }

    }

    public function getHistories(Request $request)
    {
        $histories = [];
        $date1 = Carbon::now()->subMinutes(config('constants.date.expire'));
        $date2 = Carbon::now()->subHours(config('constants.date.hours'));

        if ($request->hasHeader('xroom')) {
            $histories = DB::table('xroom_reservations')
                ->leftJoin('hotels', 'hotels.id', '=', 'xroom_reservations.hotel_id')
                ->leftJoin('room_types', 'room_types.id', '=', 'xroom_reservations.room_type_id')
                ->leftJoin('bed_types', 'bed_types.id', '=', 'room_types.bed_type_id')
                ->select(
                    'xroom_reservations.*',
                    'room_types.name as roomTypeName',
                    'room_types.occupancy',
                    'room_types.images',
                    'hotels.name as hotelName',
                    'hotels.lat',
                    'hotels.lng',
                    'hotels.phone',
                    'bed_types.name as bedTypeName',
                    DB::raw('coalesce(hotels.short_address, hotels.address) as address')
                )
                ->where('client_id', $request->header('xroom'))
                ->whereRaw(('case when xroom_reservations.payment_status = "confirmed" then xroom_reservations.created_at > "'. $date2 .'" else xroom_reservations.created_at > "'.$date1.'" end'))
                ->get();
        }

        return response()->json([
            'success' => true,
            'histories' => $histories
        ]);
    }

    public function deleteHistory(Request $request, $id)
    {
        $reservation = XRoomReservation::find($id);

        if (is_null($reservation)) {
            return response()->json([
                'success' => false,
                'message' => 'invoice not found'
            ], 400);
        }

        $reservation -> created_at = Carbon::now()->subYears(1);
        $reservation -> save();

        return response()->json([
            'success' => true,
            'message' => 'success'
        ]);
    }

    /**
     * Return a listing of hotels
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchHotels(Request $request)
    {
        try {
            $search = $request->query('query');
            $location = $request->input('location');
            $stayType = $request->input('stay_type');

            $query = Hotel::where('is_active', true)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search . '%')
                        ->where([['hotel_type_id', 5], ['has_xroom', 1]]);
                });

            if (!empty($stayType)) {
                $query->whereHas('roomTypes', function ($q) use ($stayType) {
                    if ($stayType == 'day') {
                        $q->whereNotNull('price_day_use');
                    }
                });
            }

            if (!is_null($location)) {
                $query->whereHas('commonLocations', function ($q) use ($location) {
                    $q->where('common_location_id', $location);
                });
            }

            $hotels = $query->orderBy('name', 'asc')
                ->get(['id', 'name', 'slug', 'hotel_type_id']);


            return response()->json([
                'success' => true,
                'hotels' => $hotels,
                'query' => $request->query('query'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Алдаа гарлаа. ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Nearby xroom hotels.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function nearby(Request $request)
    {
        try {

            $lat = $request->input('lat', null);
            $lng = $request->input('lng', null);
            $distance = $request->input('distance', 1);

            if (is_null($lat) || is_null($lng)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Байршлын мэдээлэл илгээнэ үү.',
                ], 400);
            }

            $lat = round($lat, 8);
            $lng = round($lng, 8);

            $rowsPerPage = (int) $request->query('rowsPerPage', 12);
            // Sort column
            $sortBy = snake_case($request->query('sortBy', 'id'));
            // Sort direction
            $direction = $request->query('direction', 'desc');

            // Query
            $searchQuery = Hotel::where([['hotel_type_id', 5], ['has_xroom', 1]])
                ->whereNotNull('location')
                ->with('commonLocations');

            $searchQuery = $searchQuery->whereNotNull('location')->with([
                'roomTypes' => function ($query) {
                    $query
                        ->whereHas('xroomType')
                        ->where('default_price', '>', 0)
                        ->where('price_time', '>', 0)
                        ->with(['bedType'])
                        ->orderBy('default_price', 'ASC');
                }
            ]);

            // Sort
            $searchQuery->orderBy($sortBy, $direction);

            if ($rowsPerPage === 0) {
                return response()->json($searchQuery->get());
            }

            if ($rowsPerPage === -1) {
                $rowsPerPage = $searchQuery->count();
            }

            $searchQuery = $searchQuery->paginate($rowsPerPage);

            $data = [];
            foreach ($searchQuery as $hotel) {
                $calculateDistance = $this->distance($lat, $lng, round($hotel->location['lat'], 8), round($hotel->location['lng'], 8), 'K');
                $formatDistance = floor(number_format((float) $calculateDistance, 2, '.', ''));
                // dd($formatDistance);
                if ($formatDistance <= $distance) {
                    $distance *= 1000;
                    $hotel->distance = $formatDistance;
                    $data[] = [
                        'id' => $hotel->id,
                        'name' => $hotel->name,
                        'companyName' => $hotel->company_name,
                        'address' => $hotel->address,
                        'image' => $hotel->image,
                        'images' => $hotel->images,
                        'description' => $hotel->description,
                        'location' => $hotel->location,
                        'lat' => $hotel->lat,
                        'lng' => $hotel->lng,
                        'distance' => $formatDistance,
                        'distance_unit' => 'km',
                        'distance_unit_plural' => 'km',
                        'roomTypes' => $hotel->roomTypes
                    ];
                }
            }

            return response()->json(["hotels" => $data], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => config('constants.log.error'),
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