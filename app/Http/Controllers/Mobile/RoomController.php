<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\{
    Reservation, RoomTypeClone
};

class RoomController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Room';
    protected $request = 'App\Http\Requests\RoomRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;
            
        return $hotel->rooms();
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->only([
            'id', 'status', 'description'
        ]);

        if ($request->filled('roomType.id')) {
            $data = array_merge($data, [
                'roomTypeId' => $request->input('roomType.id'),
            ]);
        }

        if ($request->filled('name')) {
            $data = array_merge($data, [
                'name' => $request->input('name'),
            ]);
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
        // Update room clones with clean status
        if ($request->filled('id') && $request->filled('oldStatus') && $request->input('status') === 'clean') {
            // Get dirty room clones
            $dirtyRoomClones = $model->roomClones()->where('status', 'dirty')->get();

            foreach ($dirtyRoomClones as $item) {
                $item->status = 'clean';
                $item->save();
            }
        }
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

        // If names
        if ($request->filled('names')) {
            foreach ($request->input('names') as $name) {
                $data = $this->storeOrUpdate(array_merge($this->requestParams($request), ['name' => $name]));
            }
        } else {
            $data = $this->storeOrUpdate($this->requestParams($request));
        }

        $this->afterCommit($request, $data);

        return $this->responseJSON($data);
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

        // Check room is assigned to reservations
        $exists = $model::where('id', $id)
            ->whereDoesntHave('roomClones.reservation', function ($query) {
                $query->whereIn('status', [
                    'pending', 'confirmed', 'checked-in', 'no-show',
                ]);
            })
            ->exists();

        if (!$exists) {
            return response()->json([
                'message' => 'Өрөөнд захиалгын бүртгэл үүссэн байна.',
            ], 400);
        }

        $this->newQuery()
            ->where($model->getTable() . '.id', $id)
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Return rooms as calendar resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoomsAsResources(Request $request)
    {
        // Get rooms
        $resources = $this->newQuery()
            ->join('room_types AS rt', 'rooms.room_type_id', '=', 'rt.id')
            ->select('rooms.id', 'rooms.name', 'rooms.status', 'rt.id AS roomTypeId', 'rt.name AS roomTypeName', 'rt.short_name AS roomTypeShortName')
            ->get();

        return response()->json($resources);
    }

    /**
     * Return rooms to map view with reservations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoomsMapView(Request $request)
    {
        try {
            $currentDate = $request->input('date', null);
            $rowsPerPage = $request->query('rowsPerPage', 25);

            !$currentDate && $currentDate = Carbon::now()->format('Y-m-d');
    
            // Get rooms
            $rooms = $this->newQuery()
                ->with(['blocks' => function($query) use($currentDate) {
                    $query->whereDate('start_date', $currentDate);
                }])
                ->get();

            $reservations = Reservation::
                select(['id', 'sync_id', 'status', 'check_in', 'check_out', 'hotel_id',  'room_clone_id', 'amount', 'amount_paid'])
                ->whereDate('check_in', $currentDate)
                ->where('status', '<>', 'canceled')
                ->with(['roomClone'])
                ->get();

                foreach ($rooms as $room) {
                    $isValid = false;
                    foreach ($reservations as $reservation) {
                        if ($room->id === $reservation->roomClone->room_id) {
                            $isValid = true;
                            $room->reservation = $reservation;
                            if ($reservation->balance > 0) {
                                $room->icon_url = ['/img/icons/tugrug.svg'];
                                if ($reservation->status === 'checked-in') {
                                    $room->icon_url = ['/img/icons/tugrug.svg', '/img/icons/check-in.svg'];
                                }
                            } else if($reservation->status === 'checked-in') {
                                $room->icon_url = ['/img/icons/' . $reservation->status . '.svg'];
                            }
                        }
                    }

                    if ($room->blocks->count() > 0) {
                        $room->status = 'locked';
                        $room->icon_url = ['/img/icons/' . $room->status . '.svg'];
                    } else if ($room->availableIn($currentDate, Carbon::parse($currentDate)->addDays(1)->format('Y-m-d'), $room->id)
                    ->exists() && $room->status === 'clean') {
                        !$isValid && $room->icon_url = ['/img/icons/add.svg'];
                    } else {
                        $room->icon_url = ['/img/icons/' . $room->status . '.svg'];
                    }
                }

            return response()->json($rooms);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Үйлдэл амжилтгүй. Алдаа гарлаа.',
            ], 400);
        }
    }
}
