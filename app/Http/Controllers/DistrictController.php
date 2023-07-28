<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{BelongsToAdmin};


class DistrictController extends BaseController
{
    use BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\District';
    protected $request = 'App\Http\Requests\DistrictRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::query();
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
            'id', 'name', 'code', 'international', 'isActive', 'location',
        ]);

        $data = array_merge($data, [
            'provinceId' => $request->input('province.id'),
            'countryId' => $request->input('province.countryId'),
        ]);

        return $data;
    }


}
