<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_default', 'hotel_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name'
    ];

    /**
     * Get all of users related to the role.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    /**
     * Get the hotel that related to role.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all permission that related to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission');
    }
}
