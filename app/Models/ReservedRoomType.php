<?php

namespace App\Models;

class ReservedRoomType extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reserved_room_types';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'rates' => 'array'
    ];

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
        'reservation_request_id', 'room_type_id', 'sync_id', 'name', 'short_name', 'occupancy', 'quantity', 'by_person', 'number_of_guests', 'amount', 'rates'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
    ];

    /**
     * Get the reservation request of reserved room type.
     */
    public function reservationRequest()
    {
        return $this->belongsTo('App\Models\ResReq');
    }

    /**
     * Get the room type.
     */
    public function roomType()
    {
        return $this->belongsTo('App\Models\RoomType');
    }
}
