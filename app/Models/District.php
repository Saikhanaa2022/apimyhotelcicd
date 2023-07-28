<?php

namespace App\Models;

use App\Traits\HasIhotel;

class District extends Base
{
    use HasIhotel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'districts';

    /**
     * The table associated with the model.
     *
     * @var boolean
     */
    protected $hasIhotel = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'location' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sync_id', 'name', 'international', 'code', 'country_id', 'province_id', 'image', 'location', 'is_active', 'order_no'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $hidden = [
        'sync_id'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'international', 'syncId', 'country.name', 'hotel.name', 'province.name'
    ];

    /**
     * The attributes that are appends.
     *
     * @var array
     */
    protected $appends = [
    ];

    /**
     * Get the province of the district.
     */
    public function province() {
        return $this->belongsTo('App\Models\Province');
    }

    /**
     * Get the common locations for the district
     */
    public function commonLocations() {
        return $this->hasMany('App\Models\CommonLocation');
    }
}
