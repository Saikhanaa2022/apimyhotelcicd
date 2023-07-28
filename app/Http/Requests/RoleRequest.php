<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RoleRequest
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
            ->roles()
            ->pluck('roles.id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'permissions' => 'nullable|array'
        ];
    }
}
