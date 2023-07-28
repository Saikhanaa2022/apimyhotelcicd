<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OccupancyRatePlan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'occupancy_rate_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'occupancy', 'discount_type', 'discount', 'is_active', 'rate_plan_id', 'is_default'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the rate plan associated with the occupancy rate plan.
     */
    public function ratePlan()
    {
        return $this->belongsTo('App\Models\RatePlan');
    }
}
