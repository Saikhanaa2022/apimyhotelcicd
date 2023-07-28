<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PaymentMethodRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $id = request()->input('id');
        $ids = request()
            ->hotel
            ->paymentMethods()
            ->pluck('payment_methods.id');

        return [
            'color' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'incomeTypes' => 'required|array',
            'isPaid' => 'required|boolean'
        ];
    }
}
