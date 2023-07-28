<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CurrencyRequest
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
            ->currencies()
            ->pluck('currencies.id');

        return [
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('currencies')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'shortName' => 'required|string|max:4',
            'rate' => 'required|numeric',
        ];
    }
}
