<?php

namespace App\Models;

class RatePlanClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rate_plan_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_daily' => 'boolean',
        'non_ref' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_daily', 'rate_plan_id', 'non_ref',
    ];

    /**
     * Get the rate plan associated with the rate plan clone.
     */
    public function ratePlan()
    {
        return $this->belongsTo('App\Models\RatePlan');
    }
}
