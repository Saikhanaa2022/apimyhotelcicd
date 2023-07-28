<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Reservation, RoomTypeClone
};

class CalendarController extends BaseController
{
    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return request()->hotel;
    }

    /**
     * Get all resources by date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calendarByDates(Request $request)
    {
        try {
            $isTime = $request->input('isTime', false);
            $request->validate([
                'startDate' => 'required|date_format:Y-m-d' . ($isTime ? ' H:i' : ''),
                'endDate' => 'required|date_format:Y-m-d' . ($isTime ? ' H:i' : ''),
            ]);

            $startDate = $request->query('startDate');
            $endDate = $request->query('endDate');
            $assignee = $request->query('assignee');

            // Get Hotel
            $query = $this->newQuery();

            // Count nights
            $nights = stayNights($startDate, $endDate, false);

            // Get RoomTypes
            $roomTypes = $query->roomTypes()
                ->select(['id', 'name', 'occupancy', 'occupancy_children', 'price_day_use', 'default_price'])
                ->with(['rooms' => function ($query) {
                    $query->select(['id', 'name', 'status', 'room_type_id']);
                }])->get();

            // Get Reservation by dates
            $reservations = $query->reservations()
                ->select(['id', 'sync_id', 'check_in', 'check_out', 'status', 'hotel_id', 'room_clone_id','room_type_clone_id', 'rate_plan_clone_id', 'group_id'])
                ->where([
                    ['check_in', '<=', $endDate],
                    ['check_out', '>=', $startDate],
                    ['status', '<>', 'canceled'],
                    ['status', '<>', 'no-show'],
                ])
                ->with(['roomClone' => function($query) {
                    $query->select('id', 'name', 'status', 'room_id');
                }])
                ->with(['roomTypeClone' => function($query) {
                    $query->select('id', 'sync_id', 'room_type_id', 'is_res_request','by_person');
                }])->get();

            // Count rooms by date
            foreach($roomTypes as $roomType) {
                $roomsCount = [];
                for ($i = 0; $i <= $nights; $i++) {
                    $start = Carbon::parse($startDate)->addDays($i)->format('Y-m-d');
                    $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                        
                    $roomsCount['' . $start] = $roomType->rooms()->unassigned($start, $end)->count();
                }

                $roomType->roomsCount = $roomsCount;

                $roomTypeReservations = [];
                foreach ($reservations as $reservation) {
                    if ($reservation->roomTypeClone->room_type_id === $roomType->id) {
                        $roomTypeReservations[] = $reservation;
                    }   
                }

                $roomType->reservations = $roomTypeReservations;
                $roomTypeReservations = [];
            }

            // Find unassigned rooms and room complement percent compute
            $totalRooms = $query->rooms->count();
            $unassignedRooms = [];
            $unassigned = 0;
            for ($a = 0; $a <= $nights; $a++) {
                $start = Carbon::parse($startDate)->addDays($a)->format('Y-m-d');
                $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');

                foreach($roomTypes as $roomType) {
                    $unassigned += $roomType->rooms()->unassigned($start, $end)->count();
                }

                $roomsPercent[''.$start] =  ceil((($totalRooms - $unassigned) * 100) / $totalRooms);
                $unassignedRooms[''.$start] =  $unassigned;
                $unassigned = 0;
            }

            return response()->json([
                'success' => true,
                'assigned_percent' => $roomsPercent,
                'unassigned_rooms' => $unassignedRooms,
                'roomTypes' => $roomTypes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Үйлдэл амжилтгүй. Алдаа гарлаа.',
            ], 400);
        }
    }
}
