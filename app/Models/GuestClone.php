<?php

namespace App\Models;

class GuestClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guest_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_blacklist' => 'boolean', 
        'is_primary' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'phone_number', 'email', 'passport_number', 'nationality', 'description', 'is_blacklist', 'blacklist_reason', 'is_primary', 'reservation_id', 'guest_id', 'created_at', 'updated_at',
    ];

    /**
     * Get the guest associated with the guest clone.
     */
    public function guest()
    {
        return $this->belongsTo('App\Models\Guest');
    }
}
