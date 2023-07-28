<?php

namespace App\Models;

class RoomClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_clones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'room_id', 'status'
    ];

    /**
     * Get the room type that owns the room type clone.
     */
    public function reservation()
    {
        return $this->hasOne('App\Models\Reservation');
    }

    /**
     * Get the room that owns the room clone.
     */
    public function room()
    {
        return $this->belongsTo('App\Models\Room');
    }
}
