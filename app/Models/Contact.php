<?php

namespace App\Models;

class Contact extends Base
{
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	public $table = 'contacts';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'hotel_name', 'contact_name', 'position', 'email', 'phone_number', 'feedback', 'notes'
	];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'hotel_name', 'contact_name', 'email', 'phone_number'
    ];
}
