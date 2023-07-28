<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceCategoryController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\ServiceCategory';
    protected $request = 'App\Http\Requests\ServiceCategoryRequest';
}
