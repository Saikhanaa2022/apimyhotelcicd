<?php

namespace App\Models;
use App\Traits\HasTranslation;

class RoomTypeClone extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'room_type_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
	protected $casts = [
	   'images' => 'array',
       'has_extra_bed' => 'boolean',
       'has_time' => 'boolean',
       'is_res_request' => 'boolean',
       'by_person' => 'boolean',
       'discount_percent' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sync_id', 'name', 'short_name', 'default_price', 'price_day_use', 'price_time',
        'price_time_count', 'occupancy', 'has_time', 'room_type_id', 'has_extra_bed', 'extra_beds',
        'discount_percent', 'is_res_request', 'by_person', 'sale_quantity',
    ];
    
    protected $appends = [
        'translate'
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\RatePlanTranslation', 'translation_id', 'id');
	}
    /**
     * Get the room type that owns the room type clone.
     */
    public function reservation()
    {
        return $this->hasOne('App\Models\Reservation');
    }

    /**
     * Get the room type that owns the room type clone.
     */
    public function roomType()
    {
        return $this->belongsTo('App\Models\RoomType');
    }
}
