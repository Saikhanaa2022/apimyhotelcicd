<?php

namespace App\Http\Requests;

class CancellationPolicyRequest {
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $isFree = request()->input('isFree');
        $hasCancellationTime = false;

        if (!$isFree) {
            $hasCancellationTime = request()->filled('cancellationTime.id');
        }

        return [
            'isFree' => 'required|boolean',
            'cancellationPercent.id' => 'required|integer',
            'cancellationTime.id' => $isFree ? 'required|integer' : 'nullable|integer',
            'cancellationAdditionPercent.id' => $isFree ? 'required|integer' : ($hasCancellationTime ? 'required|integer' : 'nullable|integer'),
            'hasPrepayment' => 'nullable|boolean',
        ];
    }
}
