<?php

namespace App\Models;

class Meal extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'meals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code',
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
     * Get all of the rate plans for the partner.
     */
    public function ratePlans()
    {
        return $this->belongsToMany('App\Models\RatePlan');
    }
}
