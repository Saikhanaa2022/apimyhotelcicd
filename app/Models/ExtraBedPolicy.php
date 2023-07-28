<?php

namespace App\Models;

class ExtraBedPolicy extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'extra_bed_policies';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'name',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'age_type', 'price_type', 'price', 'min', 'max', 'hotel_id',
    ];

    /**
     * Append name attribute
     */
    public function getNameAttribute()
    {
        $ageType = $this->age_type;
        if ($ageType === 'children') {
            $name = 'Хүүхэд /' . $this->min . ' - ' . $this->max . '/';
        } else if ($ageType === 'adults') {
            $name = 'Насанд хүрсэн';
        } else {
            $name = 'Бүх нас';
        }
        return $name;
    }

    /**
     * Get the rate plan associated with the occupancy rate plan.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the clones of policy.
     */
    public function extraBedPolicyClones()
    {
        return $this->hasMany('App\Models\ExtraBedPolicyClone');
    }
}
