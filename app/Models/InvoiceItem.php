<?php

namespace App\Models;

class InvoiceItem extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_items';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'amount', 'amount_tax',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id', 'date', 'name', 'item_type', 'quantity', 'price', 'contract_no',
    ];

    /**
     * Append amount attribute
     */
    public function getAmountAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Append amount tax attribute
     */
    public function getAmountTaxAttribute()
    {
        return $this->item_type === 'tax' ? $this->price * $this->quantity : 0;
    }
}
