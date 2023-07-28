<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class TaxRequest
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
            ->taxes()
            ->pluck('taxes.id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxes')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'percentage' => 'required|integer',
            'inclusive' => 'required|boolean'
        ];
    }
}
