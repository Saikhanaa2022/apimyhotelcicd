<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonLocation extends Model
{
     /**
     * The hotels that belong to the location.
     */
    public function hotels()
    {
        return $this->belongsToMany('App\Models\Hotel', 'hotel_location');
    }

    /**
     * Get the district for the location.
     */
    public function district() {
        return $this->belongsTo('App\Models\District');
    }
}
