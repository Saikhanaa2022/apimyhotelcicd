<?php

namespace App\Models;
use App\Traits\HasTranslation;

class ServiceTranslation extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_translations';

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

    public function service() {
       return $this->hasOne('App\Model\Service');
    }
}
