<?php

namespace App\Http\Requests;

class HotelSettingRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        return [
            'nightAuditTime' => 'nullable|string',
            'hasNightAudit' => 'nullable|boolean',
            'isNightauditAuto' => 'nullable|boolean',
            'hasResRequest' => 'nullable|boolean',
            'isMustPay' => 'nullable|boolean',
            'bccEmails' => 'nullable|array',
            'emailHeader' => 'nullable|string',
            'emailBody' => 'nullable|string',
            'emailFooter' => 'nullable|string',
            'emailContact' => 'nullable|string',
            'emailAttachments' => 'nullable|array'
        ];
    }
}
