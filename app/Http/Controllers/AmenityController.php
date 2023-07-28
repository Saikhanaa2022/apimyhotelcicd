<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{BelongsToAdmin};

class AmenityController extends BaseController
{
    use BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Amenity';
    protected $request = 'App\Http\Requests\AmenityRequest';

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
            'id', 'name', 'nameEn', 'image', 'amenityCategoryId',
        ]);

        // $data = array_merge($data, [
        //     'amenityCategoryId' => $request->input('amenityCategory.id'),
        // ]);

        return $data;
    }
}
