<?php

namespace App\Http\Requests;

class ItemRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'reservationId' => 'required|integer',
            'serviceCategory.id' => 'required|integer',
            'service.id' => 'required|integer',
            'service.productCategoryId' => 'required|integer'
        ];
    }
}
