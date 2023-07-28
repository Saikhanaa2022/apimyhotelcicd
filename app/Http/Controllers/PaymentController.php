<?php

namespace App\Http\Controllers;

use App\Models\{Currency, CurrencyClone, PaymentMethod, PaymentMethodClone, PaymentItem, PaymentPay, UserClone};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Payment';
    protected $request = 'App\Http\Requests\PaymentRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $this->model::whereIn('id', $hotel->payments->pluck('id'));
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($query, $request)
    {
        // Filter by incomeTypes
        if ($request->filled('incomeTypes')) {
            $query->whereIn('income_type', $request->incomeTypes);
        }

        // Get filter type from request
        if ($request->filled('filterType')) {
            $filterType = $request->input('filterType');

            if ($filterType === 'createdAt') {
                $filterField = 'created_at';
            } else if ($filterType === 'paidAt') {
                $filterField = 'posted_date';
            }

            $hasStartDate = $request->filled('startDate');
            $hasEndDate = $request->filled('endDate');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            if ($hasStartDate && $hasEndDate) {
                $query
                    ->whereDate($filterField, '>=', date($startDate))
                    ->whereDate($filterField, '<=', date($endDate));
            } else if ($hasStartDate && !$hasEndDate) {
                $query->whereDate($filterField, '>=', date($startDate));
            } else if (!$hasStartDate && $hasEndDate) {
                $query->whereDate($filterField, '<=', date($endDate));
            }
        }

        if ($request->filled('isPaid')) {
            $isPaid = $request->input('isPaid');
            if ($isPaid)
                $query->whereNotNull('paid_at');
            else
                $query->whereNull('paid_at');
        }

        return $query;
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
            'amount.gt' => 'Төлбөрийн дүн 0 -с их байх ёстой.',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Find original user
            $user = $request->user();

            // Create user clone
            $userClone = UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Find original currency
            $currency = Currency::findOrFail($request->input('currency.id'));

            // Create currency clone
            $currencyClone = CurrencyClone::create([
                'name' => $currency->name,
                'short_name' => $currency->short_name,
                'rate' => $currency->rate,
                'is_default' => $currency->is_default,
                'currency_id' => $currency->id,
            ]);

            $hotel = $request->hotel;
            // // Find rollover time
            // $currentDate = Carbon::now();
            // $nightAuditDate = Carbon::parse($hotel->hotelSetting->night_audit_time)->addDay(1);

            // // Check is rollover time passed
            // if ($hotel->has_night_audit) {
            //     // if ($currentDate->lte($nightAuditDate))
            //     // $postedDate = $hotel->working_date;
            // } else {
            //     // $postedDate = $currentDate->toDateString();
            // }

            // $postedDate = $hotel->working_date;

            $params = array_merge($request->all(), [
                'userCloneId' => $userClone->id,
                'currencyCloneId' => $currencyClone->id,
            ]);

            $data = $this->storeOrUpdate($params);

            // Commit transaction
            DB::commit();

            // if request has id delete old payments for update
            // if ($request->filled('id')) {
            //     // $data->pays()->delete();
            //     $this->updateAfterCommit($request, $data);
            // } else {
            $this->afterCommit($request, $data);
            // }

            return $this->responseJSON($data);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * After new resource created.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function afterCommit(Request $request, $model)
    {
        $incomeType = $request->incomeType;
        // if (!$request->filled('id') && $request->filled('items')) {
        //     foreach ($request->input('items') as $item) {
        //         $taxPercent = $model->reservation->taxPercentage();
        //         $price = $item['price'] + (($item['price'] * $taxPercent) / 100);

        //         // Create payment items
        //         PaymentItem::create([
        //             'payment_id' => $model->id,
        //             'name' => $item['name'],
        //             'item_id' => $item['id'],
        //             'item_type' => $item['type'],
        //             'quantity' => $item['quantity'],
        //             'price' => $price,
        //         ]);
        //     }
        // }

        // Create payment pays
        if ($request->filled('pays')) {
            foreach ($request->input('pays') as $item) {
                // Find original payment method
                $paymentMethod = PaymentMethod::findOrFail($item['paymentMethod']['id']);

                // Create payment method clone
                $paymentMethodClone = PaymentMethodClone::create([
                    'name' => $paymentMethod->name,
                    'color' => $paymentMethod->color,
                    'is_default' => $paymentMethod->is_default,
                    'income_types' => $paymentMethod->income_types,
                    'is_paid' => $paymentMethod->is_paid,
                    'payment_method_id' => $paymentMethod->id,
                ]);

                // Create payment pays
                PaymentPay::create([
                    'payment_id' => $model->id,
                    'payment_method_clone_id' => $paymentMethodClone->id,
                    'amount' => $item['amount'],
                ]);
            }
        }

        $res = $model->reservation;
        $guestClone = $res->guestClone;

        $pays = [];

        foreach ($model->pays as $pay) {
            array_push($pays, [
                'id' => $pay->id,
                'amount' => $pay->amount,
                'payment_method' => $pay->paymentMethodClone->name,
                'payment_method_clone_id' => $pay->payment_method_clone_id
            ]);
        }

        $model->income_pays = json_encode($pays, JSON_UNESCAPED_UNICODE);
        $model->paid_at = $incomeType !== 'receivable' ? Carbon::now() : null;
        $model->is_active = $incomeType !== 'receivable';
        $model->payer = json_encode([
                'name' => $guestClone->name,
                'surname' => $guestClone->surname,
                'phone_number' => $guestClone->phone_number,
                'email' => $guestClone->email,
                'passport_number' => $guestClone->passport_number,
            ], JSON_UNESCAPED_UNICODE);
        $model->update();

        // Update paid amount of reservation
        $reservation = $model->reservation;
        $reservation->amount_paid = $reservation->calcPaidAmount();
        $reservation->save();
    }

    /** FIX HERE
     * After existing resource updated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    // protected function updateAfterCommit(Request $request, $model)
    // {
    //     // Update payment pays
    //     if ($request->filled('pays')) {
    //         // Check old payment pays
    //         foreach ($model->pays as $pay) {
    //             $pays = array_filter($request->input('pays'), function ($paymentPay) use ($pay) {
    //                 return array_key_exists('id', $paymentPay) && $paymentPay['id'] && $paymentPay['id'] === $pay->id;
    //             });

    //             if (count($pays) === 0) {
    //                 $pay->delete();
    //             }
    //         }
    //         foreach ($request->input('pays') as $item) {
    //             // Create payment pays
    //             if (array_key_exists('id', $item) && $item['id']) {
    //                 // Find payment pay
    //                 $paymentPay = PaymentPay::find($item['id']);

    //                 // Find payment method clone
    //                 $paymentMethodClone = PaymentMethodClone::find($item['paymentMethodCloneId']);

    //                 // Check payment method of updated pay
    //                 if ($paymentMethodClone->id != $item['paymentMethodClone']['id']) {
    //                     $paymentMethod = PaymentMethod::findOrFail($item['paymentMethodClone']['id']);
    //                     // Update payment method clone
    //                     $paymentMethodClone->update([
    //                         'name' => $paymentMethod->name,
    //                         'color' => $paymentMethod->color,
    //                         'is_default' => $paymentMethod->is_default,
    //                         'income_types' => $paymentMethod->income_types,
    //                         'is_paid' => $paymentMethod->is_paid,
    //                         'payment_method_id' => $paymentMethod->id,
    //                     ]);
    //                 }

    //                 // Update payment pay
    //                 $paymentPay->update([
    //                     'payment_method_clone_id' => $paymentMethodClone->id,
    //                     'amount' => $item['amount'],
    //                     'notes' => $item['notes'],
    //                     'income_type' => $paymentMethodClone->is_paid ? 'paid' : $item['incomeType'],
    //                 ]);
    //             } else {
    //                 // Find original payment method
    //                 $paymentMethod = PaymentMethod::findOrFail($item['paymentMethodClone']['id']);

    //                 // Create payment method clone
    //                 $paymentMethodClone = PaymentMethodClone::create([
    //                     'name' => $paymentMethod->name,
    //                     'color' => $paymentMethod->color,
    //                     'is_default' => $paymentMethod->is_default,
    //                     'income_types' => $paymentMethod->income_types,
    //                     'is_paid' => $paymentMethod->is_paid,
    //                     'payment_method_id' => $paymentMethod->id,
    //                 ]);

    //                 // Create payment pay
    //                 PaymentPay::create([
    //                     'payment_id' => $model->id,
    //                     'payment_method_clone_id' => $paymentMethodClone->id,
    //                     'amount' => $item['amount'],
    //                     'notes' => $item['notes'],
    //                     'income_type' => $paymentMethod->is_paid ? 'paid' : $item['incomeType'],
    //                 ]);
    //             }
    //         }
    //     }
    // }

    /** FIX HERE
     * Send payment bill request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function paymentBill(Request $request, $id)
    // {
    //     // Validate request
    //     $this->validator($request->all(), 'saveBillTypeRules');

    //     // MySQL transaction
    //     DB::beginTransaction();

    //     try {
    //         // Find payment with id
    //         $payment = $this->model::findOrFail($id);

    //         $payment->bill_type = $request->input('billType');
    //         $payment->save();

    //         // Commit transaction
    //         DB::commit();

    //         // $this->afterCommit($request, $data);

    //         return $this->responseJSON($payment);
    //     } catch (Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
    //         ], 400);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Declare new model
        $model = new $this->model();

        $data = $this->newQuery()
            ->where($model->getTable() . '.id', $id)
            ->first();

        if (!is_null($data)) {
            // Check has ref id
            if (!is_null($data->ref_id)) {
                return response()->json([
                    'message' => 'Something went wrong. Try again.'
                ], 400);
            }
            // Check is audited
            if ($data->is_audited) {
                return response()->json([
                    'message' => trans('messages.payment_audited', ['date' => $data->posted_date]),
                ], 400);
            }

            $data->delete();

            $this->afterDelete($data);

            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    /**
     * After resource deleted.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function afterDelete($model)
    {
        $reservation = $model->reservation;
        // Get reservation then update amount paid
        $reservation->amount_paid = $reservation->calcPaidAmount();
        $reservation->update();
    }

    /**
     * Create the resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'createPaymentRules', [
            'amount.gt' => 'Төлбөрийн дүн 0 -с их байх ёстой.',
        ]);

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Get reference id
            $refId = $request->input('refId');
            $hotel = $request->hotel;
            // Find original user
            $user = $request->user();

            // Create user clone
            $userClone = UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Get reference payment
            $refPayment = $this->model::find($refId);

            // Find default currency
            $currency = $hotel->currencies()->where('is_default', true)->first();

            // Create currency clone
            $currencyClone = CurrencyClone::create([
                'name' => $currency->name,
                'short_name' => $currency->short_name,
                'rate' => $currency->rate,
                'is_default' => $currency->is_default,
                'currency_id' => $currency->id,
            ]);

            $params = array_merge($request->all(), [
                'userCloneId' => $userClone->id,
                'currencyCloneId' => $currencyClone->id,
                'reservationId' => $refPayment->reservation_id,
                'incomeType' => 'paid',
                'paidAt' => Carbon::now(),
                'billType' => null,
            ]);

            $data = $this->storeOrUpdate($params);

            // Commit transaction
            DB::commit();

            // Find original payment method
            $paymentMethod = PaymentMethod::findOrFail($request->input('paymentMethodId'));

            // Create payment method clone
            $paymentMethodClone = PaymentMethodClone::create([
                'name' => $paymentMethod->name,
                'color' => $paymentMethod->color,
                'is_default' => $paymentMethod->is_default,
                'income_types' => $paymentMethod->income_types,
                'is_paid' => $paymentMethod->is_paid,
                'payment_method_id' => $paymentMethod->id,
            ]);

            // Create payment pays
            PaymentPay::create([
                'payment_id' => $data->id,
                'payment_method_clone_id' => $paymentMethodClone->id,
                'amount' => $data->amount,
            ]);

            $res = $data->reservation;
            $guestClone = $res->guestClone;

            $pays = [];

            foreach ($data->pays as $pay) {
                array_push($pays, [
                    'id' => $pay->id,
                    'amount' => $pay->amount,
                    'payment_method' => $pay->paymentMethodClone->name,
                    'payment_method_clone_id' => $pay->payment_method_clone_id
                ]);
            }

            $data->income_pays = json_encode($pays, JSON_UNESCAPED_UNICODE);
            $data->payer = json_encode([
                    'name' => $guestClone->name,
                    'surname' => $guestClone->surname,
                    'phone_number' => $guestClone->phone_number,
                    'email' => $guestClone->email,
                    'passport_number' => $guestClone->passport_number,
                ], JSON_UNESCAPED_UNICODE);
            $data->update();

            // Update paid amount of reservation
            $res->amount_paid = $res->calcPaidAmount();
            $res->update();

            // Update ref payment
            $refPayment->paid_at = $data->paid_at;
            $refPayment->update();

            return $this->responseJSON($data);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

    /**
     * Custom update payment.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'updatePaymentRules');

        // Find data
        $data = $this->newQuery()
            ->find($request->input('id'));

        if ($data) {
            $payer = $request->input('payer');
            $data->payer = json_encode($payer, JSON_UNESCAPED_UNICODE);
            $data->notes = $request->input('notes');

            $data->update();
        } else {
            return response()->json([
                'message' => 'Record not found.',
                'success' => false
            ], 404);
        }

        return response()->json([
            'message' => 'Successfully updated.',
            'success' => true
        ]);
    }
}
