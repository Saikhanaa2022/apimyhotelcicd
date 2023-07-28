<?php

namespace App\Http\Requests;

class PaymentRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        // $isUpdate = request()->filled('id');
        $incomeType = request()->incomeType;

        return [
            // Check income type
            'postedDate' => 'required|date',
            'notes' => ($incomeType == 'receivable' ? 'required' : 'nullable') . '|string',
            'incomeType' => 'required|string|in:receivable,prepaid,paid',
            'amount' => 'nullable|integer|gt:0',
            'reservationId' => 'required|integer',
            'currency.id' => 'required|integer',
            'pays' => 'required|array',
            'pays.*.amount' => 'required|gt:0',
            'pays.*.paymentMethod.id' => 'required|integer',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveBillTypeRules()
    {
        return [
            'billType' => 'required|string',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function createPaymentRules()
    {
        return [
            'refId' => 'required|integer',
            'postedDate' => 'required|date',
            'amount' => 'required|integer',
            'notes' => 'nullable|string',
            'paymentMethodId' => 'required|integer',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function updatePaymentRules()
    {
        return [
            'id' => 'required|integer',
            'payer.name' => 'required|string|max:255',
            'payer.surname' => 'required|string|max:255',
            'payer.phone_number' => 'required|string',
            'payer.email' => 'nullable|email',
            'notes' => 'required|string',
        ];
    }
}
