<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class PartnerRequest
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
            ->partners()
            ->pluck('partners.id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('partners')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'registerNo' => [
                'required',
                'string',
                'max:255',
                Rule::unique('partners', 'register_no')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'type' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('partners')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'contactPerson' => 'required|string|max:255',
            'financePerson' => 'nullable|string|max:255',
            'financePhoneNumber' => 'nullable|string|max:255',
            'financeEmail' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('partners', 'finance_email')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ],
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
