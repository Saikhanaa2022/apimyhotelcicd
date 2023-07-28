<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XRoomReservation extends Model
{
    //
    protected $table = 'xroom_reservations';

    protected $casts = [
        'invoice_data' => 'array'
    ];

    protected $fillable = [
        'stay_type',
        'hotel_id',
        'room_type_id',
        'room_id',
        'payment_method',
        'code'
    ];

    protected $hidden = [
        'invoice_no',
        'invoice_data'
    ];
}