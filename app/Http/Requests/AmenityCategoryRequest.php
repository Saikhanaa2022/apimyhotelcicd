<?php

namespace App\Http\Requests;

class AmenityCategoryRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
	public static function saveRules()
	{
		return [
			'name' => 'required|string|max:255',
		];
	}
}
