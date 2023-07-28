<?php

namespace App\Models;

class HotelBank extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'hotel_banks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'hotel_id', 'bank_id', 'account_name', 'number', 'currency', 'qr_image', 'is_default'
	];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean'
    ];

    /**
     * Get the hotel of hotel bank.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the bank of hotel account.
     */
    public function bank()
    {
        return $this->belongsTo('App\Models\Bank');
    }
}
