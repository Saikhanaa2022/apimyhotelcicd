<?php

namespace App\Models;
use Illuminate\Support\Carbon;

class ResReq extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservation_requests';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_group' => 'boolean',
        'is_org' => 'boolean',
        'is_paid' => 'boolean',
        'guest' => 'array',
        'age_of_children' => 'array',
        'transaction_response' => 'array',
        'is_fetch_payment' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'res_number', 'check_in', 'check_out', 'status', 'number_of_guests', 'number_of_children',
        'number_of_rooms', 'stay_type', 'stay_nights', 'age_of_children', 'amount', 'amount_paid',
        'discount_avg_percent', 'guest', 'is_group', 'is_paid', 'is_org', 'paid_at', 'notes',
        'sync_id', 'hotel_id', 'source_clone_id', 'transaction_response', 'discount_calc_type', 'commission',
        'is_fetch_payment'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'res_number', 'sourceClone.name', 'guest', 'amount', 'check_in', 'check_out',
    ];

    /**
     * Generate unique number
     *
     * @return string
     */
    public static function generateNumber()
    {
        $date = Carbon::today()
            ->format('Ymd');

        // Current date's latest reservation
        $count = self::whereDate('created_at', Carbon::today())
            ->count();

        $value = $date . sprintf('%03d', $count + 1);

        return $value;
    }

    /**
     * Get the hotel associated with the reservation.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the source clone associated with the reservation.
     */
    public function sourceClone()
    {
        return $this->belongsTo('App\Models\SourceClone');
    }

    /**
     * Get the reserved room type.
     */
    public function reservedRoomTypes()
    {
        return $this->hasMany('App\Models\ReservedRoomType', 'reservation_request_id', 'id');
    }

    /**
     * Get the reservations.
     */
    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation', 'res_req_id', 'id');
    }
}
