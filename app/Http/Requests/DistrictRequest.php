<?php

namespace App\Http\Requests;

class DistrictRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public static function saveRules()
	{
		return [
			'name' => 'required|string|max:255|unique:districts,name,' . request()->input('id'),
			'province.id' => 'required|integer',
			'province.countryId' => 'required|integer',
            'code' => 'nullable|string',
            'international' => 'nullable|string',
            'location.lat' => 'required|numeric',
            'location.lng' => 'required|numeric',
            'isActive' => 'nullable|boolean',
		];
	}
}
