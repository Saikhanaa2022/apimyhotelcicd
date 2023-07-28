<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use \App\Models\Room;

class BlockController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Block';
    protected $request = 'App\Http\Requests\BlockRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $this->model::whereIn('room_id', $hotel->rooms()->pluck('rooms.id'));
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
        if ($request->filled('filterType')) {
            $filterType = $request->input('filterType');
            if ($filterType === 'createdAt') {
                $filterField = 'created_at';
            } else if ($filterType === 'startDate') {
                $filterField = 'start_date';
            } else if ($filterType === 'endDate') {
                $filterField = 'end_date';
            } else {
                $filterField = '';
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query
                    ->whereDate($filterField, '>=', date($request->input('startDate')))
                    ->whereDate($filterField, '<=', date($request->input('endDate')));
            } else if ($request->filled('startDate') && !$request->filled('endDate')) {
                $query->whereDate($filterField, '>=', date($request->input('startDate')));
            } else if (!$request->filled('startDate') && $request->filled('endDate')) {
                $query->whereDate($filterField, '<=', date($request->input('endDate')));
            }
        }

        return $query;
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
            'id', 'description', 'roomId',
            //'isTime',
        ]);

        return array_merge($data, [
            'rooms' => $request->input('rooms'),
            'endDate' => $request->input('endDate'),
            'startDate' => $request->input('startDate'),
            // 'startDate' => $request->input('startDate') . ($data['isTime'] ? ' ' . $request->input('startTime') : ''),
            // 'endDate' => $request->input('endDate') . ($data['isTime'] ? ' ' . $request->input('endTime') : ''),
        ]);
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

        $unavailableRooms = [];
        $params = $this->requestParams($request);
        $rooms = collect($request->input('rooms', []))->toArray();

        if ($request->filled('id')) {
            // Find room
            $room = Room::where('id', $request->input('roomId'))->first();
            $roomCheck = Room::where('id', $request->input('roomId'));
            $block = \App\Models\Block::where('id', $request->input('id'))->first();

            if (!$roomCheck->unassigned($params['startDate'], $params['endDate'])->exists()) {
                if (!($block->room->id === $room->id)) {
                    array_push($unavailableRooms, $room->name);
                }
            }
        } else {
            foreach ($rooms as $room) {
                $exists = Room::where('id', $room['id'])
                    ->unassigned($params['startDate'], $params['endDate'], $room['id'])
                    ->exists();
                if (!$exists) {
                    array_push($unavailableRooms, $room['name']);
                }
            }
        }

        // Error message
        if (count($unavailableRooms) > 0) {
            return response()->json([
                'message' => implode(', ', $unavailableRooms) . ' тоот өрөөг хаах боломжгүй байна.',
            ], 400);
        }

        // Save room and room id
        if ($params['rooms']) {
            foreach ($params['rooms'] as $room) {
                $collection = collect($params)->put('roomId', $room['id'])->toArray();
                $data = $this->storeOrUpdate($collection);
            }
        } else {
            $data = $this->storeOrUpdate($params);
        }

        $this->afterCommit($request, $data);

        return response()->json([
            'success' => true,
        ]);
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
        $roomType = $model->room->roomType;
        // Get roomType sync id
        $roomSyncId = $roomType->sync_id;
        // Check this resource hotel connected to ihotel
        if ($roomType->hotel->has_ihotel && !is_null($roomSyncId)) {
            // Block related room on ihotel
            try {
                // Send request to ihotel
                $http = new Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/manage/block', [
                    'json' => [
                        'id' => is_null($model->sync_id) ? null : $model->sync_id,
                        'syncId' => $model->id,
                        'roomSyncId' => $roomSyncId,
                        'startDate' => $model->start_date,
                        'endDate' => $model->end_date,
                        'number' => 1,
                        'isReserved' => false,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $res = json_decode((string) $response->getBody(), true);

                // Get status
                if ($res['status'] == true) {
                    $model->sync_id = $res['syncId'];
                    $model->save();
                }
            } catch (RequestException $e) {
            }
        }
    }

    /**
     * Before resource deleted.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */

    protected function beforeDelete($model)
    {
        // Check model has sync id
        if ($model->sync_id) {
            // Delete block on ihotel
            try {
                // Send request to ihotel
                $http = new Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/delete/block', [
                    'json' => [
                        'id' => $model->sync_id,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);
            } catch (RequestException $e) {
            }
        }
    }

    protected function beforeMassDelete($request)
    {
        // Check model has sync id
        if (!is_null($request->input('ids', null))) {
            // Delete block on ihotel
            try {
                // Send request to ihotel
                $http = new Client;
                $sync_ids = $this->model::whereIn('id', $request->input('ids'))->get()->pluck('sync_id');
                $response = $http->post(config('services.ihotel.baseUrl') . '/delete/blocks', [
                    'json' => [
                        'ids' => $sync_ids,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);
                return json_decode((string) $response->getBody(), true);
            } catch (RequestException $e) {
                return $e->getMessage();
            }
        }
    }

    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);
        $response = $this->beforeMassDelete($request);

        // Declare new model
        $model = new $this->model();

        $this->newQuery()
            ->whereIn($model->getTable() . '.id', $request->input('ids'))
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /** FIX THIS
     * Get all resources by date.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByDates(Request $request)
    {
        $request->validate([
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
        ]);

        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $blocks = $this->newQuery()
            ->where([
                ['start_date', '<=', $endDate],
                ['end_date', '>=', $startDate],
            ])
            ->get(['id', 'room_id', 'start_date', 'end_date', 'is_time']);

        // Load relations
        if ($request->has('with')) {
            $blocks->load($request->query('with'));
        }

        return response()->json([
            'blocks' => $blocks,
        ]);
    }
}
