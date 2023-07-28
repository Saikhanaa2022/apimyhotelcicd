<?php

namespace App\Http\Requests;

class ReservationRequest
{
    protected static $statuses = 'pending,confirmed,canceled,checked-in,checked-out,no-show';
    protected static $stayTypes = 'night,day,time';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'id' => 'required|integer',
            'arrivalTime' => 'nullable|date_format:H:i',
            'exitTime' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
            'status' => 'sometimes|required|string|max:255|in:' . self::$statuses,
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function updateMultipleRules()
    {
        return [
            'reservations' => 'required|array',
            'reservations.*' => 'required|integer',
            'status' => 'sometimes|required|string|max:255|in:' . self::$statuses,
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveMultipleRules()
    {
        $stayType = request()->input('stayType', 'night');
        $isAutoFill = request()->input('isAutoFill', false);
        $isPriceRequired = $stayType !== 'night';
        $isTimeRequired = $stayType === 'time';

        return [
            'gid' => 'nullable|integer',
            'stayType' => 'required|string|in:' . self::$stayTypes,
            'checkIn' => 'required|date_format:Y-m-d H:i',
            'checkOut' => 'required|date_format:Y-m-d H:i',
            'resTime' => ($isTimeRequired ? 'required' : 'nullable') . '|integer',
            'arrivalTime' => 'nullable|date_format:H:i',
            'exitTime' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string',
            'source.id' => 'required|integer',
            'partner.id' => 'nullable|integer',
            'guest.id' => 'nullable|integer',
            'guest.name' => 'required|string|max:255',
            'guest.surname' => 'nullable|string|max:255',
            'guest.phoneNumber' => 'nullable|integer',
            'guest.email' => 'nullable|string|email|max:255',
            'guest.passportNumber' => 'nullable|string|max:255',
            'guest.nationality' => 'nullable|string|max:255',
            'roomTypes' => ($isAutoFill ? 'nullable' : 'required') . '|array',
            'roomTypes.*.id' => ($isAutoFill ? 'nullable' : 'required') . '|integer',
            'roomTypes.*.ratePlan.id' => 'nullable|integer',
            'roomTypes.*.numberOfGuests' => ($isAutoFill ? 'nullable' : 'required') . '|integer',
            'roomTypes.*.numberOfChildren' => ($isAutoFill ? 'nullable' : 'required') . '|integer',
            'roomTypes.*.ageOfChildren' => 'nullable|array',
            'roomTypes.*.quantity' => ($isAutoFill ? 'nullable' : 'required') . '|integer',
            'roomTypes.*.timePrice' => ($isPriceRequired ? 'required|gt:0' : 'nullable') . '|integer',
        ];
    }
}
