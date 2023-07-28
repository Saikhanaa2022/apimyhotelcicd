<?php

namespace App\Http\Requests;

class CancellationTimeRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'name' => 'required|string',
            'hasTime' => 'required|boolean',
            'day' => 'required|integer',
        ];
    }
}
