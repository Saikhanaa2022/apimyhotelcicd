<?php

namespace App\Http\Requests;

class MealRequest
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
            'code' => 'required|string|max:255',
        ];
    }
}
