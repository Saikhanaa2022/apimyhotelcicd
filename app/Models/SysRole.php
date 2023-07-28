<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysRole extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sys_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code'
    ];

    /**
     * Get the users of sys role.
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }
}
