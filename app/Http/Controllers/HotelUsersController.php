<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;

class HotelUsersController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\User';
    protected $request = 'App\Http\Requests\UserRequest';

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
        $data = $request->only([
            'id', 'name', 'position', 'phoneNumber', 'email',
        ]);

        return $data;
    }

    /**
     * Store or update the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        // Validate request
        // $this->validator($request->all(), 'saveRules');

        DB::beginTransaction();
        try{

            $data = $this->storeOrUpdate($this->requestParams($request));

            // $permissionIds = collect($request->input('permissions'))->pluck('id');

            // Sync permissions
            // $data->permissions()->sync($permissionIds);

            // Commit transaction
            DB::commit();
            
            // Register event
            event(new Registered($data));

            return $this->responseJSON($data);
        }catch (Exception $e){
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
             
        }

        return $this->responseJSON($data);
    }
}
