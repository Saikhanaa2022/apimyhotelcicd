<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class HotelRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $hasCountry  = request('countryId');
        $hasProvince = request('provinceId');

        return [
            'name' => 'required|string|max:255',
            'registerNo' => 'nullable|string',
            'isCitypayer' => 'required|boolean',
            'isVatpayer' => 'required|boolean',
            'companyName' => 'nullable|string|max:255',
            'hotelTypeId' => 'required|integer',
            'email' => 'nullable|email|max:255',
            'resEmail' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'maxTime' => 'nullable|integer',
            'rules' => 'nullable|string',
            'provinceId' => ($hasCountry ? 'required' : 'nullable') . '|integer',
            'districtId' => (($hasCountry || $hasProvince) ? 'required' : 'nullable') . '|integer',
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
    public static function syncFacilitiesRules()
    {
        return [
            'facilities' => 'nullable|array',
            'facilities.*.id' => 'required|integer',
        ];
    }

    /**
     * Sync hotel to wuBook validation rules.
     *
     * @return array
     */
    public static function syncWuBookRules()
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'resEmail' => 'required|email|max:255',
            'website' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'zipCode' => 'required|integer',
            'countryId' => 'required|integer',
            'provinceId' => 'required|integer',
            'districtId' => 'required|integer',
        ];
    }

    /**
     * Fetch hotel data from ihotel.mn validation rules.
     *
     * @return array
     */
    public static function fetchHotelRules()
    {
        $id = request()->hotel->id;
        $ids = \App\Models\Hotel::all()->pluck('id');
        return [
            'ihotelId' => [
                'required',
                'integer',
                Rule::unique('hotels', 'sync_id')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ]
        ];
    }

    /**
     * Sync hotel to ihotel.mn validation rules.
     *
     * @return array
     */
    public static function syncHotelRules()
    {
        return [
            'ihotelId' => 'required|integer',
            'hasIhotel' => 'nullable|boolean',
            'hasResRequest' => 'nullable|boolean',
            'roomTypes' => 'required|array',
            'roomTypes.*.syncId' => 'nullable|integer',
            // unique:room_types,sync_id
        ];
    }

    /**
     * Connect hotel to xroom.mn validation rules.
     *
     * @return array
     */
    public static function syncXRoomRules()
    {
        return [
            'hasXroom' => 'nullable|boolean',
            'hasResRequest' => 'nullable|boolean',
            'rooms' => 'required|array',
        ];
    }

    /**
     * Update hotel online book validation rules.
     *
     * @return array
     */
    public static function onlineBookRules()
    {
        return [
            'hasOnlineBook' => 'required|boolean',
        ];
    }

    /**
     * Update hotel chatbot validation rules.
     *
     * @return array
     */
    public static function chatbotRules()
    {
        return [
            'hasChatbot' => 'required|boolean',
        ];
    }

    /**
     * Update hotel chatbot validation rules.
     *
     * @return array
     */
    public static function sourceRules()
    {
        $hasRoom  = request('hasRoom');
        return [
            'hasSource' => 'required|boolean',
            'hotelId' => 'required|numeric',
            'ihotelId' => 'required|numeric',
            'roomTypes' => (($hasRoom) ? 'required' : 'nullable') . '|array',
            'service_name' => 'required|string',
        ];
    }
}
