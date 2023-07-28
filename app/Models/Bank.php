<?php

namespace App\Models;

class Bank extends Base
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'has_qr' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'logo', 'is_active', 'has_qr', 'qr_type',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'qr_type',
    ];

	/**
     * Get the hotel account that related to bank.
     */
	public function hotelBanks()
    {
        return $this->hasMany('App\Models\HotelBank');
    }
}
