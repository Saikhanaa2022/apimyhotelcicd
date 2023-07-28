<?php

namespace App\Http\Requests;

class BankRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $hasQr = request('hasQr');

        return [
            'name' => 'required|string|max:120',
            'logo' => 'nullable|string|max:255',
            'isActive' => 'required|boolean',
            'hasQr' => 'required|boolean',
            'qrType' => ($hasQr ? 'required' : 'nullable') . '|string|in:qpay,socialpay',
        ];
    }
}
