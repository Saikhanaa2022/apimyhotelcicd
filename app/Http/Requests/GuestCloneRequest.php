<?php

namespace App\Http\Requests;

class GuestCloneRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'phoneNumber' => 'nullable|integer',
            'email' => 'nullable|string|email|max:255',
            'passportNumber' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'isBlacklist' => 'nullable|boolean',
            'blacklistReason' => 'nullable|string',
            'reservationId' => 'required|integer',
        ];
    }
}
