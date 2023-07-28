<?php

namespace App\Http\Requests;

class InvoiceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'groupId' => 'required|integer',
            'email' => 'nullable|email',
            // 'hotel.image' => 'nullable|string|max:255',
            // 'hotel.name' => 'required|string|max:255',
            // 'hotel.registerNo' =>  'required|string|max:255',
            // 'hotel.address' =>  'required|string|max:255',
            // 'hotel.phoneNumber' =>  'required|string|max:255',
            // 'hotel.email' => 'required|email|max:255',
            // 'hotel.companyName' => 'required|string|max:255',
            'customer.customerName' => 'nullable|string|max:255',
            'customer.registerNo' => 'nullable|string|max:255',
            'customer.address' => 'nullable|string|max:255',
            'customer.phoneNumber' => 'nullable|string|max:255',
            'customer.contractNo' => 'nullable|string|max:255',
            'customer.tourCode' => 'nullable|string|max:255',
            'customer.voucherCode' => 'nullable|string|max:255',
            'customer.invoiceDate' => 'required|date_format:Y-m-d',
            'customer.paymentPeriod' => 'required|date_format:Y-m-d',
            'items' => 'required|array',
        ];
    }
}
