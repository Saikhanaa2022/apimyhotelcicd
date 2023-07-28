<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{BelongsToAdmin};

class FacilityCategoryController extends BaseController
{
    use BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\FacilityCategory';
    protected $request = 'App\Http\Requests\FacilityCategoryRequest';

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
        return $request->all();
    }
}
