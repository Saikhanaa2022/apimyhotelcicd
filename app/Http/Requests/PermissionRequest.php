<?php

namespace App\Http\Requests;

class PermissionRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
        ];
    }
}