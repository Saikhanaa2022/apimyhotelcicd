<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Base
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payments';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payer' => 'array',
        'income_pays' => 'array',
        'is_active' => 'boolean',
        'is_audited' => 'boolean',
        'is_ignored' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'total_amount',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'id', 'payer', 'reservation.number', 'notes', 'amount'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notes', 'posted_date', 'income_type', 'income_pays', 'paid_at', 'payer', 'amount', 'currency_clone_id', 'user_clone_id', 'reservation_id', 'ref_id', 'bill_type', 'is_active', 'is_audited', 'is_ignored', 'ignored_reason', 'deleted_at'
    ];

    /**
     * Append total amount attribute
     */
    // public function getTotalAmountAttribute()
    // {
    //     return $this->amount * $this->currencyClone->rate;
    // }

    /**
     * Calculate total amount.
     */
    public function calculate()
    {
        // $total = $this->items->sum('amount');
        $total = $this->amount;
        $total = $total + calculatePercent($total, $this->reservation->taxPercentage());
        return $total;
    }

    /**
     * Get the payment method clone associated with the payment.
     */
    // public function paymentMethodClone()
    // {
    //     return $this->belongsTo('App\Models\PaymentMethodClone');
    // }

    /**
     * Get the currency clone associated with the payment.
     */
    public function currencyClone()
    {
        return $this->belongsTo('App\Models\CurrencyClone');
    }

    /**
     * Get the user clone associated with the payment.
     */
    public function userClone()
    {
        return $this->belongsTo('App\Models\UserClone');
    }

    /**
     * Get the payment bills associated with the payment.
     */
    public function items()
    {
        return $this->hasMany('App\Models\PaymentItem');
    }

    /**
     * Get the payment pays associated with the payment.
     */
    public function pays()
    {
        return $this->hasMany('App\Models\PaymentPay');
    }

    /**
     * Get the reservation associated with the payment.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation')
            ->select(['id', 'number', 'check_in', 'check_out', 'status', 'amount', 'amount_paid']);
    }
}
