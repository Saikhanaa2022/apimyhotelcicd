<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IntervalController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Interval';
    protected $request = 'App\Http\Requests\IntervalRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        $ratePlanIds = $hotel->ratePlans()
            ->pluck('rate_plans.id');

        return \App\Models\Interval::whereIn('rate_plan_id', $ratePlanIds);
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
            'id', 'name', 'startDate', 'endDate', 'minLos', 'maxLos',
        ]);

        return array_merge($data, [
            'ratePlanId' => $request->input('ratePlan.id'),
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

        $params = $this->requestParams($request);

        // Check any interval exists in range
        $exists = $this->newQuery()
            ->when($request->filled('id'), function ($query) use ($params) {
                return $query->where('id', '<>', $params['id']);
            })
            ->where([
                ['rate_plan_id', $params['ratePlanId']],
                ['start_date', '<=', $params['endDate']],
                ['end_date', '>=', $params['startDate']],
            ])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Сонгосон огноонд үнэ тохируулсан байна.',
            ], 400);
        }

        $interval = $this->storeOrUpdate($params);

        // Sync rates
        $interval->syncRates($request);
        
        return $this->responseJSON($interval);
    }
}
