<?php

namespace App\Models;

class HotelRule extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'hotel_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'hotel_id', 'title', 'description',
	];

    /**
     * Get the hotel of hotel bank.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }
}
