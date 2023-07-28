<?php

namespace App\Http\Requests;

class ContactRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'hotelName' => 'required|string',
            'contactName' => 'required|string',
            'position' => 'required|string',
            'email' => 'required|string|email',
            'phoneNumber' => 'required|string',
            'feedback' => 'nullable|string',
            'notes' => 'nullable|string'
        ];
    }
}
