<?php

namespace App\Http\Requests;

class PaymentItemRequest
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
            'item.id' => 'required|integer',
            'item_type' => 'required|integer',
            'quantity' => 'required|integer',
            'price' => 'required|integer',
        ];
    }
}
