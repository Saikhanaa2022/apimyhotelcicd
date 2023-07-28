<?php

namespace App\Http\Requests;

class LanguageRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'name' => 'required|string|max:30',
            'name_national' => 'nullable|string',
            'locale' => 'required|string|max:2',
        ];
    }
}
