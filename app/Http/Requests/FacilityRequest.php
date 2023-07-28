<?php

namespace App\Http\Requests;

class FacilityRequest
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
			'image' => 'nullable|string|max:255',
			'facilityCategoryId' => 'required|integer',
		];
	}
}
