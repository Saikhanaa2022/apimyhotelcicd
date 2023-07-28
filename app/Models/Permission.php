<?php

namespace App\Models;

use App\Traits\HasTranslation;

class Permission extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permissions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_property' => 'boolean',
    ];

    protected $appends = [
        'translate'
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\PermissionTranslation', 'translation_id', 'id');
	}

    /**
     * Get all of the roles that related to the permission.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }
}
