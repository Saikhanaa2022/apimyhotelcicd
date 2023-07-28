<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CurrencyController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Currency';
    protected $request = 'App\Http\Requests\CurrencyRequest';
}
