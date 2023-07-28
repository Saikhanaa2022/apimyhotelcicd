<?php

namespace App\Http\Requests;

class ResReqRequest
{
    /**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public static function saveRules()
	{
		return [
			'sync_id' => 'required|integer',
		];
	}
}
