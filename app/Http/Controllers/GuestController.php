<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GuestController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Guest';
    protected $request = 'App\Http\Requests\GuestRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($query, $request)
    {
        if ($request->has('blacklistFilter')) {
            $query->whereIn('is_blacklist', $request->input('blacklistFilter'));
        }

        $hasStartDate = $request->filled('startDate');
        $hasEndDate = $request->filled('endDate');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        if ($hasStartDate && $hasEndDate) {
            $query
                ->whereDate('created_at', '>=', date($startDate))
                ->whereDate('created_at', '<=', date($endDate));
        } else if ($hasStartDate && !$hasEndDate) {
            $query->whereDate('created_at', '>=', date($startDate));
        } else if (!$hasStartDate && $hasEndDate) {
            $query->whereDate('created_at', '<=', date($endDate));
        }

        return $query;
    }
}
