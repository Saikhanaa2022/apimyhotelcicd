<?php

namespace App\Http\Requests;

class ExtraBedRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'amount' => 'required|integer',
            'nights' => 'required|integer|gt:0',
            'policy.id' => 'required|integer',
            'reservation.id' => 'required|integer',
        ];
    }
}
