<?php

namespace App\Http\Requests;

class BlockRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        // $isTime = request()->input('isTime', false);
        $roomsRequired = request()->filled('id') ? 'nullable' : 'required';
        $roomIdRequired = request()->filled('id') ? 'required' : 'nullable';

        return [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            // 'startTime' => ($isTime ? 'required' : 'nullable'), '|date_format:H:i',
            // 'endTime' => ($isTime ? 'required' : 'nullable'), '|date_format:H:i',
            'description' => 'nullable|string',
            'rooms' => $roomsRequired . '|array',
            'rooms.*.id' => $roomsRequired . '|integer',
            'roomId' => $roomIdRequired . '|integer',
        ];
    }
}
