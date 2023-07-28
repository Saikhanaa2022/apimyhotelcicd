<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{BelongsToAdmin, BelongsToManyHotel};

class FacilityController extends BaseController
{
    use BelongsToManyHotel, BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Facility';
    protected $request = 'App\Http\Requests\FacilityRequest';

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
            'id', 'name', 'image', 'facilityCategoryId',
        ]);

        // $data = array_merge($data, [
        //     'facilityCategoryId' => $request->input('facilityCategory.id'),
        // ]);

        return $data;
    }
}
