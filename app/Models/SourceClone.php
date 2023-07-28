<?php

namespace App\Models;

class SourceClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'source_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'short_name', 'color', 'is_default', 'is_active', 'service_name', 'source_id',
    ];

    /**
     * Get the source associated with the source clone.
     */
    public function source()
    {
        return $this->belongsTo('App\Models\Source');
    }
}
