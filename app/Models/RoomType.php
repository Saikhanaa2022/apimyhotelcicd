<?php

namespace App\Models;

use App\Traits\HasTranslation;

class RoomType extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_types';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
	protected $casts = [
	   'images' => 'array',
       'has_extra_bed' => 'boolean',
       'has_time' => 'boolean',
       'is_online' => 'boolean',
       'is_res_request' => 'boolean',
       'by_person' => 'boolean',
       'discount_percent' => 'array',
       'days' => 'array'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sync_id', 'name', 'short_name', 'default_price', 'price_day_use', 'price_time', 'price_time_count', 'window', 'occupancy', 'is_online', 'has_time',
        'hotel_id', 'images', 'has_extra_bed', 'extra_beds', 'occupancy_children', 'floor_size', 'description', 'bed_type_id',
        'discount_percent', 'is_res_request', 'by_person', 'sale_quantity','start_date','end_date','days'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'short_name'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'online_price',
        'translate',
    ];

    /**
     * Append online price attribute
     */
    // public function getOnlinePriceAttribute()
    // {
    //     $hotel = $this->hotel()->first();
    //     // Calculate enabled and inclusive tax percent
    //     $taxPercent = $hotel->taxes()
    //         ->where('is_enabled', true)
    //         ->where('inclusive', false)
    //         ->sum('percentage');

    //     // Check rate plan price of

    //     $price = $this->default_price + calculatePercent($this->default_price, $taxPercent);
    //     return $price;
    // }

    public function availableRoomsCount($check_in, $check_out)
    {
        return $this
            ->rooms()
            ->unassigned($check_in, $check_out)
            ->count();
    }

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\RoomTypeTranslation', 'translation_id', 'id');
	}

    /**
     * Get the room type's hotel.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the rate plans for the room type.
     */
    public function ratePlans()
    {
        return $this->hasMany('App\Models\RatePlan');
    }

    /**
     * Get the rooms for the room type.
     */
    public function rooms()
    {
        return $this->hasMany('App\Models\Room');
    }

    /**
     * Get all of the clones for the room type.
     */
    public function roomTypeClones()
    {
        return $this->hasMany('App\Models\RoomTypeClone');
    }

    /**
     * Get amenities for the room type.
     */
    public function amenities()
    {
        return $this->belongsToMany('App\Models\Amenity');
    }

    /**
     * Get the room type's bed type.
     */
    public function bedType()
    {
        return $this->belongsTo('App\Models\BedType');
    }

    /**
     * Get the xroom's type.
     */
    public function xroomType()
    {
        return $this->hasOne('App\Models\XRoomRoomTypes');
    }
}
