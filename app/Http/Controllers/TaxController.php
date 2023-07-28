<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaxController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Tax';
    protected $request = 'App\Http\Requests\TaxRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->taxes()->where('is_enabled', true);
    }
}
