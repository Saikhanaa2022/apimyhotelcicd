<?php

namespace App\Http\Requests;

class CancellationPercentRequest
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
            'percent' => 'nullable|integer',
            'isFirstNight' => 'required|boolean'
        ];
    }
}
