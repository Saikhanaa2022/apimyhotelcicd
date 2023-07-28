<?php

namespace App\Models;

class PaymentMethodClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_method_clones';
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_paid' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'color', 'is_default', 'is_paid', 'payment_method_id',
    ];

    /**
     * Get the payment method associated with the payment method clone.
     */
    public function paymentMethod()
    {
        return $this->belongsTo('App\Models\PaymentMethod');
    }
}
