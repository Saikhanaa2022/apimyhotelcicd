<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelRuleController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\HotelRule';
    protected $request = 'App\Http\Requests\HotelRuleRequest';

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->only([
            'id', 'title', 'description',
        ]);

        return array_merge($data, [
            'hotelId' => $request->hotel->id,
        ]);
    }
}
