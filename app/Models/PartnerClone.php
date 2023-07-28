<?php

namespace App\Models;

class PartnerClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partner_clones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'register_no', 'contact_person', 'phone_number', 'email', 'finance_person', 'finance_phone_number', 'finance_email', 'address', 'description', 'discount', 'partner_id',
    ];

    /**
     * Get the partner associated with the partner clone.
     */
    public function partner()
    {
        return $this->belongsTo('App\Models\Partner');
    }
}
