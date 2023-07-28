<?php

namespace App\Models;

class PaymentPay extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_pays';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_id', 'payment_method_clone_id', 'amount', 'notes', 'income_type'
    ];

    /**
     * Get the payment associated with the payment bill.
     */
    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    /**
     * Get the payment method clone associated with the payment pay.
     */
    public function paymentMethodClone()
    {
        return $this->belongsTo('App\Models\PaymentMethodClone');
    }
}
