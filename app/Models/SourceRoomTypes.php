<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceRoomTypes extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'source_room_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id', 'room_type_id', 'source_id', 'sale_quantity', 'active'
    ];

    /**
     * Get the xroom type's hotel.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the room type.
     */
    public function roomType()
    {
        return $this->belongsTo('App\Models\RoomType');
    }
    /**
     * Get the room type.
     */
    public function sources()
    {
        return $this->belongsTo('App\Models\Source');
    }
}
