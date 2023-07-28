<?php

namespace App\Models;

class ChildrenPolicy extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'children_policies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'age_type', 'price_type', 'price', 'min', 'max', 'hotel_id',
    ];

    /**
     * Get the rate plan associated with the occupancy rate plan.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }
}
