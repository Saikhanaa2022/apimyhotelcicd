<?php

namespace App\Models;
use App\Traits\HasTranslation;

class PaymentMethod extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_methods';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'income_types' => 'array',
        'is_paid' => 'boolean'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'color', 'is_default', 'income_types', 'is_paid', 'hotel_id',
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
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\PaymentMethodTranslation', 'translation_id', 'id');
	}

    /**
     * Get the hotel associated with the payment method.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the clones for the payment method.
     */
    public function paymentMethodClones()
    {
        return $this->hasMany('App\Models\PaymentMethodClone');
    }

    /**
     * Get all of the payments for the payment method.
     */
    // public function payments()
    // {
    //     return $this->hasManyThrough('App\Models\Payment', 'App\Models\PaymentMethodClone');
    // }

    /**
     * Get all of the payments for the payment method.
     */
    public function paymentPays()
    {
        return $this->hasManyThrough('App\Models\PaymentPay', 'App\Models\PaymentMethodClone');
    }
}
