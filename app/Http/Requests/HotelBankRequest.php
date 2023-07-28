<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class HotelBankRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $id = request()->input('id');
        $ids = request()
            ->hotel
            ->hotelBanks()
            ->pluck('hotel_banks.id');

        return [
            'bank.id' => [
                'required',
                'integer',
                Rule::unique('hotel_banks', 'bank_id')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'accountName' => 'required|string|max:255',
            'number' => 'required|integer',
            'currency' => 'required|string|max:3',
            'qrImage' => 'nullable|string',
        ];
    }
}
