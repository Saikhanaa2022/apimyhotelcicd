<?php

namespace App\Models;

class Partner extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partners';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'register_no', 'type', 'contact_person', 'phone_number', 'email', 'finance_person', 'finance_phone_number', 'finance_email', 'address', 'description', 'discount', 'hotel_id',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
    ];
    
    /**
     * Get the hotel associated with the partner.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the rate plans for the partner.
     */
    public function ratePlans()
    {
        return $this->belongsToMany('App\Models\RatePlan');
    }

    /**
     * Get all of the clones for the partner.
     */
    public function partnerClones()
    {
        return $this->hasMany('App\Models\PartnerClone');
    }
}
