<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxCloneController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\TaxClone';
    protected $request = 'App\Http\Requests\TaxCloneRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $this->model::whereIn('reservation_id', $hotel->reservations()->pluck('id'));
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        // Find original tax
        $tax = \App\Models\Tax::findOrFail($request->input('taxId'));

        return [
            'name' => $tax->name,
            'percentage' => $tax->percentage,
            'inclusive' => $tax->inclusive,
            'key' => $tax->key,
            'is_default' => $tax->is_default,
            'is_enabled' => $tax->is_enabled,
            'reservationId' => $request->reservationId,
            'taxId' => $tax->id,
        ];
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

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Create or update
            $data = $this->storeOrUpdate($this->requestParams($request));

            if (!$data->inclusive) {
                // Update reservation amount
                $reservation = $data->reservation;
                $reservation->update([
                    'amount' => $reservation->calculate(),
                ]);

                // Update reservation payments amount
                foreach ($reservation->payments as $payment) {
                    $payment->update([
                        'amount' => $payment->calculate(),
                    ]);
                }
            }

            // Commit transaction
            DB::commit();

            return $this->responseJSON($data);
        } catch (\Exception $e) {
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
                ->where('tax_clones.id', $id)
                ->firstOrFail();

            // Find tax clone's reservation
            $reservation = $data->reservation;
            // Delete tax clone
            $data->delete();

            // Update reservation amount
            $reservation->update([
                'amount' => $reservation->calculate(),
            ]);

            // Update reservation payments amount
            foreach ($reservation->payments as $payment) {
                $payment->update([
                    'amount' => $payment->calculate(),
                ]);
            }

            // Commit transaction
            DB::commit();

            return response()->json([
                'success' => true,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
}
