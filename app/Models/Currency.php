<?php

namespace App\Models;

class Currency extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'currencies';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'short_name', 'rate', 'is_default', 'hotel_id',
    ];
        
    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'short_name',
    ];

    /**
     * Get the hotel associated with the room.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the clones for the currency.
     */
    public function currencyClones()
    {
        return $this->hasMany('App\Models\CurrencyClone');
    }
}
