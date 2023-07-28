<?php

namespace App\Models;

class Charge extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'charges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notes', 'amount', 'user_clone_id', 'reservation_id',
    ];
    
    /**
     * Get the reservation associated with the item.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }

    /**
     * Get the user clone associated with the payment.
     */
    public function userClone()
    {
        return $this->belongsTo('App\Models\UserClone');
    }
}
