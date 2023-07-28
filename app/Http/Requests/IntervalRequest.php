<?php

namespace App\Http\Requests;

class IntervalRequest
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
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'minLos' => 'nullable|integer',
            'maxLos' => 'nullable|integer',
            'ratePlan.id' => 'required|integer',
            // Rates
            'mondayRate' => 'nullable|integer',
            'tuesdayRate' => 'nullable|integer',
            'wednesdayRate' => 'nullable|integer',
            'thursdayRate' => 'nullable|integer',
            'fridayRate' => 'nullable|integer',
            'saturdayRate' => 'nullable|integer',
            'sundayRate' => 'nullable|integer',
        ];
    }
}
