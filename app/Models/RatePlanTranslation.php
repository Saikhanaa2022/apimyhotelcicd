<?php

namespace App\Models;
use App\Traits\HasTranslation;

class RatePlanTranslation extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rate_plan_translations';

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

    public function ratePlan() {
        return $this->hasOne('App\Model\RatePlan');
    }
}
