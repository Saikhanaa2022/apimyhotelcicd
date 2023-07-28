<?php

namespace App\Models;

use App\Traits\HasTranslation;

class PaymentItem extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_items';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'amount', 'translate'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_id', 'name', 'item_id', 'item_type', 'quantity', 'price',
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\PaymentItemTranslation', 'translation_id', 'id');
	}

    /**
     * Append amount attribute
     */
    public function getAmountAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the payment associated with the payment bill.
     */
    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }
}
