<?php

namespace App\Models;

class ServiceClone extends Base
{
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_clones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'price', 'quantity', 'countable', 'partner_id', 'service_id',
    ];

    /**
     * Get the service that owns the service clone.
     */
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
