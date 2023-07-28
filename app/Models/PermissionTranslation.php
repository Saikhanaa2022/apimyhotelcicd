<?php

namespace App\Models;
class PermissionTranslation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'permission_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
    ];

   public function permission() {
       return $this->hasOne('App\Model\Permission');
   }
}
