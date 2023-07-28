<?php

namespace App\Models;

class UserClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_clones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'position', 'phone_number', 'email', 'user_id',
    ];

    /**
     * Get the user associated with the user clone.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
