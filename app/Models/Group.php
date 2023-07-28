<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'hotel_id', 'external_id'
    ];

    /**
     * Get the hotel associated with the group.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the reservations for the group.
     */
    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation');
    }

    /**
     * Generate unique number
     *
     * @return string
     */
    public static function generateUnique($length = 6)
    {
        $value = rand(pow(10, $length - 1), pow(10, $length) - 1);
        $exists = self::where('number', $value)
            ->exists();

        if ($exists) {
            $value = self::generateUnique();
        }

        return $value;
    }

    public function reservation_payment_method()
    {
        return $this->hasOne('App\Models\ReservationPaymentMethod');
    }
}
