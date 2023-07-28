<?php

namespace App\Http\Requests;

class ChilrenPolicyRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'ageType' => 'required|string',
            'priceType' => 'required|string',
            'price' => 'required|integer',
            'min' => 'required|integer',
            'max' => 'required|integer',
        ];
    }
}
