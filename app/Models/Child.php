<?php

namespace App\Models;

class Child extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'children';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'age', 'amount', 'reservation_id',
    ];

    /**
     * Get the reservation associated with the item.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }
}
