<?php

namespace App\Models;

use App\Traits\HasIhotel;

class Country extends Base
{
    use HasIhotel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'countries';

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
        'is_active' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sync_id', 'name', 'code', 'international', 'locale',  'curriency_id', 'image', 'location', 'is_active', 'order_no'
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
        'name', 'international', 'syncId', 'locale'
    ];

    /**
     * The attributes that are appends.
     *
     * @var array
     */
    protected $appends = [

    ];

    /**
     * Get the Country associated with the provinces.
     */
    public function provinces() {
        return $this->hasMany('App\Models\Province');
    }
}
