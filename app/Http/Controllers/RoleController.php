<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Role';
    protected $request = 'App\Http\Requests\RoleRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->roles();
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
            'id', 'name'
        ]);

        if (!$request->filled('id')) {
            $data = array_merge($data, [
                'hotelId' => $request->hotel->id,
            ]);
        }

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
        $this->validator($request->all(), 'saveRules');

        DB::beginTransaction();
        try{

            $data = $this->storeOrUpdate($this->requestParams($request));

            $permissionIds = collect($request->input('permissions'))->pluck('id');

            // Sync permissions
            $data->permissions()->sync($permissionIds);

            // Commit transaction
            DB::commit();

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
