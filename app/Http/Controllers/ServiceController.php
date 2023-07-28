<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Service';
    protected $request = 'App\Http\Requests\ServiceRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->services();
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
            'id', 'name', 'price', 'quantity', 'countable', 'barCode'
        ]);

        return array_merge($data, [
            'partnerId' => $request->input('partner.id'),
            'serviceCategoryId' => $request->input('serviceCategory.id'),
            'productCategoryId' => $request->input('productCategory.id'),
        ]);
    }
}
