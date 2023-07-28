<?php

namespace App\Http\Requests;

class PaymentPayRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'payment.id' => 'required|integer',
            'paymentMethod.id' => 'required|integer',
            'amount' => 'required|integer',
            'notes' => 'nullable|string',
            'incomeType' => 'nullable|in:receivable,prepaid'
        ];
    }
}
