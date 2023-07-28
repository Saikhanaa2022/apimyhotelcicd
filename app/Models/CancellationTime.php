<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CancellationTime extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cancellation_times';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_time' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'has_time', 'day',
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
     * Get all of the cancellation policies for the cancellation time.
     */
    public function cancellationPolicies()
    {
        return $this->hasMany('App\Models\CancellationPolicy');
    }
}
