<?php

namespace App\Models;

class HotelType extends Base
{

	/**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'hotel_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name', 'image',
	];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
	protected $searchable = [
        'name'
	];

	/**
	 * The attributes that are appends with relation.
	 *
	 * @var array
	 */
	protected $with = [
        //
	];

    /**
     * Get the hotels for the hotel type.
     */
    public function hotels()
    {
        return $this->hasMany('App\Models\Hotel');
    }
}
