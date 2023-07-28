<?php

namespace App\Http\Requests;

class BedTypeRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'name' => 'required|string|max:120',
            'nameEn' => 'nullable|string|max:120',
            'bedCount' => 'required|integer',
        ];
    }
}
