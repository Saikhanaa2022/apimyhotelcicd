<?php

namespace App\Models;

class ExtraBed extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'extra_beds';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_amount',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'nights', 'extra_bed_policy_clone_id', 'user_clone_id', 'reservation_id',
    ];

    /**
     * Append total amount attribute
     */
    public function getTotalAmountAttribute()
    {
        return $this->amount * $this->nights;
    }

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

    /**
     * Get the user clone associated with the payment.
     */
    public function extraBedPolicyClone()
    {
        return $this->belongsTo('App\Models\ExtraBedPolicyClone');
    }
}
