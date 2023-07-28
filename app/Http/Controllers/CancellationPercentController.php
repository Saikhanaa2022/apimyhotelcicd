<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CancellationPercentController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\CancellationPercent';
    protected $request = 'App\Http\Requests\CancellationPercentRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::query();
    }
}
