<?php

namespace App\Http\Controllers;

use App\Models\{Service, ServiceClone, ServiceCategory, ServiceCategoryClone, UserClone};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatePlanItemController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\RatePlanItem';
    protected $request = 'App\Http\Requests\RatePlanItemRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::query();
    }

    /**
     * Store or update the resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'saveRules', [
            'service.productCategoryId.required' => 'Үйлчилгээнд бүтээгдэхүүний ангилал тохируулаагүй байна.',
        ]);

        $params = $request->only([
            'id', 'price', 'quantity',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Find original user
            $user = $request->user();

            // Find original service
            $service = Service::findOrFail($request->input('service.id'));

            // Find original service category
            $serviceCategory = ServiceCategory::findOrFail($request->input('serviceCategory.id'));

            $params = array_merge($params, [
                'serviceId' => $service->id,
                'serviceCategoryId' => $serviceCategory->id,
                'ratePlanId' => $request->input('ratePlan.id'),
            ]);

            $data = $this->storeOrUpdate($params);

            // Commit transaction
            DB::commit();

            return $this->responseJSON($data);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
}
