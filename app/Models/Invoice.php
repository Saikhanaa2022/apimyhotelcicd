<?php

namespace App\Models;

class Invoice extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoices';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_sent' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'total_amount', 'full_total_amount',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id', 'reservation_id', 'hotel_id', 'email', 'reservation_number', 'hotel_image', 'hotel_name', 'hotel_register_no', 'hotel_address', 'hotel_phone_number', 'hotel_email', 'hotel_company_name', 'hotel_banks', 'guest_name',  'guest_surname', 'customer_name', 'register_no', 'address', 'phone_number', 'contract_no', 'tour_code', 'voucher_code', 'invoice_date', 'payment_period', 'is_sent'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'customer_name', 'reservation_number',
    ];

    /**
     * Append total amount attribute
     */
    public function getTotalAmountAttribute()
    {
        return $this->invoiceItems->sum('amount') - $this->invoiceItems->sum('amount_tax');
    }

    /**
     * Append full total amount attribute
     */
    public function getFullTotalAmountAttribute()
    {
        return $this->invoiceItems->sum('amount');
    }

    /**
     * Get the invoice items for the invoice.
     */
    public function invoiceItems()
    {
        return $this->hasMany('App\Models\InvoiceItem');
    }

    /**
     * Get the group associated with the invoice.
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }

    /**
     * Get the reservation associated with the invoice.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }

    /**
     * Get the hotel associated with the invoice.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotels');
    }
}
