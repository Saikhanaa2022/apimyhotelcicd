<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ChannelRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $id = request()->input('id');
        $ids = \App\Models\Channel::all()->pluck('id');

        return [
            'name' => 'required|string|max:30',
            'code' => 'required|string|max:15',
            'currency' => 'required|string',
            'wubookId' => [
                'nullable',
                'integer',
                'min:0',
                Rule::unique('channels', 'wubook_id')->where(function ($query) use ($ids) {
                    return $query->whereIn('id', $ids);
                })->ignore($id)
            ]
        ];
    }
}
