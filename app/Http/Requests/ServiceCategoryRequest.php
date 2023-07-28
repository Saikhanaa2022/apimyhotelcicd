<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ServiceCategoryRequest
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
            ->serviceCategories()
            ->pluck('service_categories.id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('service_categories')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ]
        ];
    }
}
