<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentMethodController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\PaymentMethod';
    protected $request = 'App\Http\Requests\PaymentMethodRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($query, $request)
    {
        if ($request->filled('isPaid')) {
            $query->where('is_paid', $request->input('isPaid'));
        }

        return $query;
    }
}
