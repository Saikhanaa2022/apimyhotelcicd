<?php

namespace App\Models;

class DayRate extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'day_rates';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'double',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date', 'value', 'default_value', 'reservation_id',
    ];

    /**
     * Get the reservation associated with the dayRate.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }
}
