<?php

namespace App\Models;

class Room extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rooms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'status', 'description', 'room_type_id', 'is_day', 'is_night', 'is_available', 'room_order'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'roomType',
    ];

    /**
     * Scope a query to only include available rooms.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $start
     * @param string $end
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableIn($query, $start, $end, $id = null)
    {
        return $query->whereDoesntHave('blocks', function ($query) use ($start, $end, $id) {
            $query->where([
                ['start_date', '<', $end],
                ['end_date', '>', $start],
            ])->when($id, function ($query) use ($id) {
                return $query->whereNotIn('id', [
                    $id
                ]);
            });
        });
    }

    /**
     * Scope a query to only include available rooms.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $start
     * @param string $end
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnassigned($query, $start, $end, $id = null, $blockId = null)
    {
        return $query->availableIn($start, $end, $blockId)
            ->whereDoesntHave('roomClones.reservation', function ($query) use ($start, $end, $id) {
                $query->where([
                    ['check_in', '<', $end],
                    ['check_out', '>', $start],
                ])->whereIn('status', [
                    'pending', 'confirmed', 'checked-in', 'no-show',
                ])->when($id, function ($query) use ($id) {
                    return $query->whereNotIn('id', [
                        $id
                    ]);
                });
            });
    }

    /**
     * Get the blocks for the room.
     */
    public function blocks()
    {
        return $this->hasMany('App\Models\Block');
    }

    /**
     * Get the room type that owns the room.
     */
    public function roomType()
    {
        return $this->belongsTo('App\Models\RoomType');
    }

    /**
     * Get all of the clones for the room.
     */
    public function roomClones()
    {
        return $this->hasMany('App\Models\RoomClone');
    }
}
