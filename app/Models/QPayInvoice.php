<?php

namespace App\Models;

class QPayInvoice extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'qpay_invoices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'room_id', 'room_type_id', 'amount', 'payment_method','number',
        'qpay_qrcode', 'qpay_qrimage', 'qpay_url', 'qpay_urls',
        'qpay_qrimage_base64', 'qpay_invoice_id', 'qpay_transaction',
        'token', 'stay_type', 'paid'
    ];
}
