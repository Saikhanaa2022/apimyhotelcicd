<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RatePlanRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $validations = [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255|in:intensive,daily',
            'nonRef' => 'nullable|boolean',
            'isOta' => 'nullable|boolean',
            'isOnlineBook' => 'nullable|boolean',
            'partners' => 'nullable|array',
            'meals' => 'nullable|array',
            'services' => 'nullable|array',
            'roomType.id' => 'required|integer',
        ];

        $roomType = null;
        $ids = [];

        if (request()->input('isOta') || request()->input('isOnlineBook')) {
            $roomType = \App\Models\RoomType::find(request()->input('roomType.id'));
            $ids = $roomType->ratePlans()->pluck('id');
        }
        // Request has ota
        if (request()->input('isOta')) {
            $validations = array_merge($validations, [
                'isOta' => Rule::unique('rate_plans', 'is_ota')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore(request()->input('id'))
            ]);
        }
        // Request has online book
        if (request()->input('isOnlineBook')) {
            $validations = array_merge($validations, [
                'isOnlineBook' => Rule::unique('rate_plans', 'is_online_book')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore(request()->input('id'))
            ]);
        }

        return $validations;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveDailyRatesRules()
    {
        return [
            'days' => 'required|array',
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d',
            'minLos' => 'nullable|integer',
            'maxLos' => 'nullable|integer',
            'rate' => 'nullable|integer',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function updateOccupancyPlansRules()
    {
        return [
            'occupancyRatePlans' => 'required|array',
            'occupancyRatePlans.*.id' => 'required|integer',
            'occupancyRatePlans.*.ratePlanId' => 'required|integer',
            'occupancyRatePlans.*.occupancy' => 'required|integer',
            'occupancyRatePlans.*.discountType' => 'required|string',
            'occupancyRatePlans.*.discount' => 'required|integer',
            'occupancyRatePlans.*.isActive' => 'required|boolean',
        ];
    }
}
