<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermissionController extends BaseController
{

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Permission';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::where('is_property', 1)
            ->select('id', 'name');
    }
}
