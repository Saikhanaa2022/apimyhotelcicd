<?php

namespace App\Http\Requests;

class ServiceRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $isDefault = false;
        $id = request()->input('serviceCategory.id');
        if(!is_null($id)) {

            $isDefault = request()
                ->hotel
                ->serviceCategories()
                ->find($id)
                ->is_default;
        }

        return [
            'name' => 'required|string|max:255',
            'price' => 'nullable|integer',
            'quantity' => 'nullable|integer',
            'countable' => 'nullable|boolean',
            'barCode' => 'nullable|string',
            'serviceCategory.id' => 'required|integer',
            'productCategory.id' => $isDefault ? 'nullable|integer' : 'required|integer',
        ];
    }
}
