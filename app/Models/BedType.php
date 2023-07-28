<?php

namespace App\Models;

class BedType extends Base
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'name_en', 'bed_count',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'name_en',
    ];

	/**
     * Get the room types that related to bed type.
     */
	public function roomTypes()
    {
        return $this->hasMany('App\Models\RoomType');
    }
}
