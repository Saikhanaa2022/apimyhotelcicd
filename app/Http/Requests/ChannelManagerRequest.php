<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ChannelManagerRequest
{
    protected static $statuses = 'pending,confirmed,canceled,checked-in,checked-out,no-show';
    protected static $stayTypes = 'night,day,time';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function availabilityRules()
    {
        return [
            'languageCode' => 'required|string',
            'stay.checkIn' => 'required|date_format:Y-m-d',
            'stay.checkOut' => 'required|date_format:Y-m-d',
            'numberOfUnits' => 'required|integer'
        ];
    }

    public static function saveReservation()
    {
        return [
            'languageCode'  => 'required|string',
            'tripReservationId' => 'required|string',
            'reservationType' => 'required|string',
            'hotelCode'       => 'required|string',
            'stay.checkIn'  => 'required|date_format:Y-m-d',
            'stay.checkOut' => 'required|date_format:Y-m-d',
            'numberOfUnits' => 'required|integer',
            'occupancyPerRoom' => 'required|integer',
            'occupancies' => 'required|array',
            'roomTypeCode' => 'required|string',
            'rateCategory' => 'required|string',
            'dailyRates' => 'required|array',
            'currency' => 'required|string',
            'contactInfo.tripEmail' => 'required|string',
            'contactInfo.tripPhone' => 'required|string',
            'contactInfo.tripContact' => 'required|string',
            'paymentInfo.whoCollect' => 'required|string',
            'coupons' => 'nullable|array'
        ];
    }

    public static function cancellation()
    {
        return [
            'languageCode'  => 'required|string',
            'tripReservationId' => 'required|string',
            'supplierReservationId' => 'required|string',
            'hotelCode'       => 'required|string'
        ];
    }
}
