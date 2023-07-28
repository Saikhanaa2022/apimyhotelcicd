<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelSettingController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\HotelSetting';
    protected $request = 'App\Http\Requests\HotelSettingRequest';

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->all();

        return array_merge($data, [
            'hotelId' => $request->hotel->id,
        ]);
    }
}
