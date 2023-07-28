<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use \App\Models\Reservation;

class Guest extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guests';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_blacklist' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'phone_number', 'email', 'passport_number', 'nationality', 'description', 'is_blacklist', 'blacklist_reason', 'hotel_id', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'surname', 'email', 'passport_number', 'phone_number',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'res_count'
    ];

    /**
     * Get the hotel associated with the guest.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the clones for the guest.
     */
    public function guestClones()
    {
        return $this->hasMany('App\Models\GuestClone');
    }

    /**
     * Append reservations count attribute
     */
    public function getResCountAttribute() {
        $reservationsIds = $this->guestClones()->pluck('reservation_id');
        return Reservation::whereIn('id', $reservationsIds)
            ->distinct('group_id')
            ->count('group_id');
    }
}
