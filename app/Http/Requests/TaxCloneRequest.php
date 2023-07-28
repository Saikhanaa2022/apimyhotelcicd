<?php

namespace App\Http\Requests;

class TaxCloneRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'taxId' => 'required|integer',
            'reservationId' => 'required|integer',
        ];
    }
}
