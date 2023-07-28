<?php

namespace App\Http\Requests;

class ChargeRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'notes' => 'nullable|string',
            'amount' => 'required|integer',
            'reservation.id' => 'required|integer',
        ];
    }
}
