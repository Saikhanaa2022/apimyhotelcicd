<?php

namespace App\Models;

class ServiceCategoryClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_category_clones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'service_category_id',
    ];

    /**
     * Get the service category for the service category clone.
     */
    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\ServiceCategory');
    }
}
