<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChargeController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Charge';
    protected $request = 'App\Http\Requests\ChargeRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->charges();
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
            'amount', 'notes',
        ]);

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

            $params = array_merge($params, [
                'userCloneId' => $userClone->id,
                'reservationId' => $request->input('reservation.id'),
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
                ->where('charges.id', $id)
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
