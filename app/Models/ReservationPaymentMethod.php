<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationPaymentMethod extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservation_payment_methods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'res_id', 'token', 'hotel_id', 'group_id', 'amount', 'reservation_id', 'payment_method', 'lend_invoice_number', 'lend_qr_string',
        'lend_url', 'qpay_qrcode', 'qpay_qrimage', 'qpay_url', 'qpay_qrimage_base64', 'qpay_invoice_id', 'qpay_qr_text', 'qpay_urls', 'mc_trans_id',
        'mc_qrcode', 'paid', 'trans_hotel_created'
    ];

    /**
     * Get the hotel associated with the group.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }

    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }
}
