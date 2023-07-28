<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RoomTypeRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $ids = request()
            ->hotel
            ->rooms()
            ->pluck('rooms.id');

        $roomTypeId = request()->input('id');
        $roomTypeIds = request()->hotel
                        ->roomTypes()
                        ->pluck('room_types.id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('room_types')->where(function ($query) use ($roomTypeIds) {
                    return $query->whereIn('id', $roomTypeIds);
                })->ignore($roomTypeId)
            ],
            'shortName' => 'required|string|max:5',
            'defaultPrice' => 'required|integer',
            'priceDayUse' => 'nullable|integer',
            'occupancy' => 'required|integer',
            'occupancyChildren' => 'nullable|integer',
            'hasExtraBed' => 'nullable|boolean',
            'extraBeds' => 'nullable|integer',
            'description' => 'nullable|string|max:255',
            'bedTypeId' => 'required|integer',
            'hasTime' => 'nullable|boolean',
            'byPerson' => 'nullable|boolean',
            // Unique name
            'names.*' => Rule::unique('rooms', 'name')->where(function ($query) use ($ids) {
                return $query->whereIn('id', $ids);
            })
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveImagesRules()
    {
        return [
            'images' => 'nullable|array',
            'images.*' => 'required|string',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function syncAmenitiesRules()
    {
        return [
            'amenities' => 'nullable|array',
            'amenities.*.id' => 'required|integer',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function searchRoomType()
    {
        return [
            'checkIn' => 'required|date_format:Y-m-d H:i',
            'checkOut' => 'required|date_format:Y-m-d H:i',
            'stayType' => 'required|in:night',
        ];
    }
}
