<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtraBedController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\ExtraBed';
    protected $request = 'App\Http\Requests\ExtraBedRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->extraBeds();
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
        $this->validator($request->all(), 'saveRules');

        $params = $request->only([
            'amount', 'nights'
        ]);

        // Get reservation
        $reservation = \App\Models\Reservation::find($request->input('reservation.id'));

        // Get room type clone then and extraBeds
        $roomTypeClone = $reservation->roomTypeClone;

        // Check hasExtraBed
        if ($roomTypeClone->has_extra_bed === 1) {
            return response()->json([
                'message' => 'Захиалгын бүртгэл үүссэн өрөөнд нэмэлт орны тохируулга хийгдээгүй байна.',
            ], 400);
        }

        // Check nights
        if ($params['nights'] > $reservation->stayNights) {
            return response()->json([
                'message' => 'Нэмэлт ор байрших хугацаа захиалгын нийт хугацаанаас их утгатай байна.',
            ], 400);
        }

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Find original user
            $user = $request->user();

            // Create user clone
            $userClone = \App\Models\UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Get a original extra bed policy
            $extraBedPolicy = \App\Models\ExtraBedPolicy::find($request->input('policy.id'));

            // Create user clone
            $extraBedPolicyClone = \App\Models\ExtraBedPolicyClone::create([
                'age_type' => $extraBedPolicy->age_type,
                'price_type' => $extraBedPolicy->price_type,
                'price' => $extraBedPolicy->price,
                'min' => $extraBedPolicy->min,
                'max' => $extraBedPolicy->max,
                'extra_bed_policy_id' => $extraBedPolicy->id,
            ]);

            $params = array_merge($params, [
                'extraBedPolicyCloneId' => $extraBedPolicyClone->id,
                'userCloneId' => $userClone->id,
                'reservationId' => $request->input('reservation.id')
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
                ->where('extra_beds.id', $id)
                ->firstOrFail();

            // Find item's reservation
            $reservation = $data->reservation;
            // Delete item
            $data->delete();

            // Update reservation amount
            $reservation->update([
                'amount' => $reservation->calculate(),
                // 'balance' =>
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
