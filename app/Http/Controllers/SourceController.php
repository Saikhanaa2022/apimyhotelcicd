<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SourceController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Source';
    protected $request = 'App\Http\Requests\SourceRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;
        return $hotel->sources()
            ->where('is_active', true)
            ->orderBy('is_default', 'DESC');
    }
}
