<?php

namespace App\Models;

class PaymentItemTranslation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_item_translations';

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

    public function paymentItem() {
        return $this->hasOne('App\Model\PaymentItem');
    }
}
