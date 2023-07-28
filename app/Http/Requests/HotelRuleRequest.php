<?php

namespace App\Http\Requests;

class HotelRuleRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'title' => 'required|string',
            'description' => 'nullable|string',
        ];
    }
}

