<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UserRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phoneNumber' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . request()->input('id'),
            'role.id' => 'required|integer',
            'password' => (request()->filled('id') ? 'nullable' : 'required') . '|string|min:6|same:passwordConfirmation|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[.#?!@$%^&*-]).{6,}$/',
        ];

        return $rules;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function updateCurrentUserRules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phoneNumber' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . request()->input('id'),
            'password' => (request()->filled('id') ? 'nullable' : 'required') . '|string|min:6|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[.#?!@$%^&*-]).{6,}$/',
        ];

        return $rules;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function sendNotificationRules()
    {
        return [
            'message' => 'required|string',
            'event' => 'required|in:created,update,deleted,sent,failed',
            'type' => 'required|in:message,reservation,resRequest',
            'notifyType' => 'required|in:broadcast',
            'notifyDataId' => 'nullable|integer',
            'emails' => 'array',
            // 'ids' => 'array',
        ];
    }
}
