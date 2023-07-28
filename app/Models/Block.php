<?php

namespace App\Models;

class Block extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'blocks';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_time' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
       'block_times',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start_date', 'end_date', 'is_time', 'description', 'room_id', 'sync_id',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'room.name',
    ];

    /**
     * Append stay nights attribute
     */
    public function getBlockTimesAttribute()
    {
        return stayNights($this->start_date, $this->end_date, $this->is_time, true);
    }

    /**
     * Get the room associated with the block.
     */
    public function room()
    {
        return $this->belongsTo('App\Models\Room');
    }
}
