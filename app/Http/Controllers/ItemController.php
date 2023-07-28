<?php

namespace App\Http\Controllers;

use App\Models\{Service, ServiceClone, ServiceCategory, ServiceCategoryClone, UserClone};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Item';
    protected $request = 'App\Http\Requests\ItemRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->items();
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
            'price', 'quantity', 'reservationId',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Find original user
            $user = $request->user();

            // Find original service
            $service = Service::findOrFail($request->input('service.id'));

            // Check item quantity
            if ($service->countable) {
                if ($service->quantity < $request->input('quantity')) {
                    return response()->json([
                        'message' => 'Бүтээгдэхүүний үлдэгдэл хүрэлцэхгүй байна.'
                    ], 400);
                } else {
                    $service->quantity = $service->quantity - $request->input('quantity');
                    $service->save();
                }
            }

            // Create user clone
            $userClone = UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            //Create service clone
            $serviceClone = ServiceClone::create([
                'name' => $service->name,
                'price' => $service->price,
                'partner_id' => $service->partner_id,
                'service_id' => $service->id,
            ]);

            // Find original service category
            $serviceCategory = ServiceCategory::findOrFail($request->input('serviceCategory.id'));

            // Create service category clone
            $serviceCategoryClone = ServiceCategoryClone::create([
                'name' => $serviceCategory->name,
                'service_category_id' => $serviceCategory->id,
            ]);

            $params = array_merge($params, [
                'userCloneId' => $userClone->id,
                'serviceCloneId' => $serviceClone->id,
                'serviceCategoryCloneId' => $serviceCategoryClone->id,
            ]);

            $data = $this->storeOrUpdate($params);

            // Update reservation amount
            $reservation = $data->reservation;
            $reservation->update([
                'amount' => $reservation->calculate(),
            ]);

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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // MySQL transaction
        DB::beginTransaction();

        try {
            $data = $this->newQuery()
                ->where('items.id', $id)
                ->firstOrFail();

            // Find item's reservation
            $reservation = $data->reservation;
            // Delete item
            $data->delete();

            // Update reservation amount
            $reservation->update([
                'amount' => $reservation->calculate(),
            ]);

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
}
