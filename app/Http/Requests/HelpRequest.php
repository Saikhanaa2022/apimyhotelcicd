<?php

namespace App\Http\Requests;

class HelpRequest
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
            'content' => 'nullable|string',
            'url' => 'nullable|string|max:255',
        ];
    }
}
