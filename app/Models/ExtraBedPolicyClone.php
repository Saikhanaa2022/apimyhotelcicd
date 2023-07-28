<?php

namespace App\Models;

class ExtraBedPolicyClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'extra_bed_policy_clones';

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
        'age_type', 'price_type', 'price', 'min', 'max', 'extra_bed_policy_id',
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
     * Get the original policy.
     */
    public function extraBedPolicy()
    {
        return $this->belongsTo('App\Models\ExtraBedPolicy');
    }
}
