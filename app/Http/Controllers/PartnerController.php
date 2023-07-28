<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PartnerController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Partner';
    protected $request = 'App\Http\Requests\PartnerRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($query, $request)
    {
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return $query;
    }
}
