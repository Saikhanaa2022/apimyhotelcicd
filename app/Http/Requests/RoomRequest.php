<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class RoomRequest
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
            ->rooms()
            ->pluck('rooms.id');

        return [
            'id' => 'nullable|integer',
            'name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|string|max:255|in:clean,dirty',
            'description' => 'nullable|string',
            'roomType.id' => 'sometimes|required|integer',
            // Unique name
            'name' => Rule::unique('rooms')->where(function ($query) use ($ids) {
                return $query->whereIn('id', $ids);
            })->ignore($id),
            'names.*' => Rule::unique('rooms', 'name')->where(function ($query) use ($ids) {
                return $query->whereIn('id', $ids);
            })->ignore($id),
        ];
    }
}
