<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SourceRequest
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
            ->sources()
            ->pluck('sources.id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sources')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'shortName' => [
                'required',
                'string',
                'max:3',
                Rule::unique('sources', 'short_name')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'color' => 'required|string|max:255',
        ];
    }
}
