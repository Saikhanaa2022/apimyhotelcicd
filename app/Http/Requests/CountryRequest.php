<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CountryRequest
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
            'international' => 'nullable|string|max:255',
            'code' => 'nullable|string',
            'locale' => 'nullable|string',
            'isActive' => 'nullable|boolean',
        ];
    }
}
