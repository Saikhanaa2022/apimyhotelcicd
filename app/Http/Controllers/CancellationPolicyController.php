<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CancellationPolicyController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\CancellationPolicy';
    protected $request = 'App\Http\Requests\CancellationPolicyRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->cancellationPolicy();
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
            'id', 'isFree', 'hasPrepayment'
        ]);

        return array_merge($data, [
            'hotelId' => $request->hotel->id,
            'cancellationPercentId' => $request->input('cancellationPercent.id'),
            'cancellationTimeId' => $request->filled('cancellationTime.id') ? $request->input('cancellationTime.id') : null,
            'additionPercentId' => $request->filled('cancellationAdditionPercent.id') ? $request->input('cancellationAdditionPercent.id') : null,
        ]);
    }
}
