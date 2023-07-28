<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotelBankController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\HotelBank';
    protected $request = 'App\Http\Requests\HotelBankRequest';

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->only([
            'id', 'bank', 'accountName', 'number', 'currency', 'qrImage', 'isDefault'
        ]);

        return array_merge($data, [
            'hotelId' => $request->hotel->id,
            'bankId' => $request->input('bank.id'),
        ]);
    }
}
