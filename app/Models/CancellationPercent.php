<?php

namespace App\Models;

class CancellationPercent extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cancellation_percents';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_first_night' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_first_night', 'percent'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
    ];
    
    /**
     * Get all of the cancellation policies for the cancellation percent.
     */
    public function cancellationPolicies()
    {
        return $this->hasMany('App\Models\CancellationPolicy');
    }

    /**
     * Get all of the cancellation free policies for the cancellation free percent.
     */
    public function cancellationFreePolicies()
    {
        return $this->hasMany('App\Models\CancellationPolicy', 'addition_percent_id', 'id');
    }
}
