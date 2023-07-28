<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
