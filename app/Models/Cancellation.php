<?php

namespace App\Models;

class Cancellation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cancellations';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_paid' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id', 'user_clone_id', 'reservation_id', 'amount', 'is_paid'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * Get the hotel with the cancellation.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the user clone with the cancellation.
     */
    public function userClone()
    {
        return $this->belongsTo('App\Models\UserClone')->select(['position', 'name', 'email', 'phone_number']);
    }

    /**
     * Get the reservation with the cancellation.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }
}
