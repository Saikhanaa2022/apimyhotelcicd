<?php

namespace App\Models;

class Rate extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'day_of_week', 'value', 'interval_id',
    ];

    /**
     * Get the interval that owns the rate.
     */
    public function interval()
    {
        return $this->belongsTo('App\Models\Interval');
    }
}
