<?php

namespace App\Models;

class Channel extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'channels';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'currency', 'wubook_id', 'is_active', 'logo',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
	protected $searchable = [
        'name', 'code',
	];

    /**
     * Get the hotels for the channel.
     */
    public function hotels()
    {
        return $this->belongsToMany('App\Models\Hotel');
    }
}
