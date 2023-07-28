<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class Reservation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reservations';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_time' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'balance', 'stay_nights',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number', 'stay_type', 'sync_id', 'res_req_id', 'check_in', 'check_out', 'number_of_guests', 'number_of_children',
        'amount', 'amount_paid', 'discount_type', 'discount', 'arrival_time', 'exit_time', 'notes', 'checked_in_at', 'checked_out_at', 'status', 'status_at',
        'hotel_id', 'user_clone_id', 'source_clone_id', 'partner_clone_id', 'rate_plan_clone_id', 'room_type_clone_id', 'room_clone_id', 'group_id', 'external_id', 'cancellation_policy_clone_id',
        'is_time', 'created_at', 'updated_at','xroom_reservation_id'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'number', 'group.number', 'roomTypeClone.name', 'roomClone.number', 'sourceClone.name', 'partnerClone.name', 'userClone.name', 'guestClone.name', 'amount',
    ];

    /**
     * Calculate items amount.
     */
    public function itemsAmount()
    {
        return $this->items
            ->sum('amount');
    }

    /**
     * Calculate charges amount.
     */
    protected function chargesAmount()
    {
        return $this->charges
            ->sum('amount');
    }

    /**
     * Calculate children amount.
     */
    public function childrenAmount()
    {
        return $this->children()
            ->sum('amount');
    }

    /**
     * Calculate extra beds amount.
     */
    public function extraBedsAmount()
    {
        return $this->extraBeds
            ->sum('total_amount');
    }

    /**
     * Calculate occupancy amount.
     */
    public function occupancyAmount()
    {
        return $this->dayRates
            ->sum('value');
    }

    /**
     * Calculate total tax percentage.
     */
    public function taxPercentage()
    {
        return $this->taxClones()
            ->where('inclusive', false)
            ->sum('percentage');
    }

    /**
     * Calculate total amount.
     */
    public function calculate($dayRates = null, $isArray = false, $withPercent = true, $amount = 0)
    {
        if ($amount === 0) {
            if (!$isArray) {
                $amount = $dayRates
                    ? $dayRates->sum('value')
                    : $this->occupancyAmount();
            } else {
                $amount = array_sum(array_column($dayRates, 'value'));
            }
        }

        // Amount
        $total = $this->itemsAmount() + $this->extraBedsAmount() + $amount;
        // $this->chargesAmount() +
        $total = $total + ($withPercent ? calculatePercent($total, $this->taxPercentage()) : 0);

        return $total;
    }

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

        // $exists = self::where('number', $value)
        //     ->exists();

        // if ($exists) {
        //     $value = self::generateNumber();
        // }

        return $value;
    }

    /**
     * Get all of the available rooms for the reservation.
     */
    public function availableRooms()
    {
        // Room type
        $original = $this->roomTypeClone
            ->roomType;

        if (!$original) {
            return collect();
        }

        return $original
            ->rooms()
            ->unassigned($this->check_in, $this->check_out, $this->id)
            ->get(['id', 'name', 'status', 'description']);
    }

    /**
     * Calc paid amount
     */
    public function calcPaidAmount()
    {
        return $this->payments()
            ->where('is_active', 1)
            ->sum('amount');
    }

    /**
     * Append stay nights attribute
     */
    public function getBalanceAttribute()
    {
        return $this->amount - $this->amount_paid;
    }

    /**
     * Append stay nights attribute
     */
    public function getStayNightsAttribute()
    {
        return stayNights($this->check_in, $this->check_out, $this->is_time);
    }

    /**
     * Append is group attribute
     */
    public function getIsGroupAttribute()
    {
        return $this->group->reservations()->count() > 1;
    }

    /**
     * Get the hotel associated with the reservation.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the group associated with the reservation.
     */
    public function group()
    {
        return $this->belongsTo('App\Models\Group');
    }

    /**
     * Get the user clone associated with the reservation.
     */
    public function userClone()
    {
        return $this->belongsTo('App\Models\UserClone');
    }

    /**
     * Get the source clone associated with the reservation.
     */
    public function sourceClone()
    {
        return $this->belongsTo('App\Models\SourceClone');
    }

    /**
     * Get the partner clone associated with the reservation.
     */
    public function partnerClone()
    {
        return $this->belongsTo('App\Models\PartnerClone')->withDefault([
            'name' => '',
            'default' => true
        ]);
    }

    /**
     * Get the rate plan clone associated with the reservation.
     */
    public function ratePlanClone()
    {
        return $this->belongsTo('App\Models\RatePlanClone')->withDefault([
            'name' => '',
            'default' => true
        ]);
    }

    /**
     * Get the room type clone associated with the reservation.
     */
    public function roomTypeClone()
    {
        return $this->belongsTo('App\Models\RoomTypeClone');
    }

    /**
     * Get the room clone associated with the reservation.
     */
    public function roomClone()
    {
        return $this->belongsTo('App\Models\RoomClone')->withDefault([
            'name' => '',
            'default' => true
        ]);
    }

    /**
     * Get all of the guest clones for the reservation.
     */
    public function guestClones()
    {
        return $this->hasMany('App\Models\GuestClone');
    }

    /**
     * Append guest clone attribute
     */
    public function guestClone()
    {
        return $this->hasOne('App\Models\GuestClone')->whereIsPrimary(true);
    }

    /**
     * Get all of the tax clones for the reservation.
     */
    public function taxClones()
    {
        return $this->hasMany('App\Models\TaxClone');
    }

    /**
     * Get all of the day rates for the reservation.
     */
    public function dayRates()
    {
        return $this->hasMany('App\Models\DayRate');
    }

    /**
     * Get all of the payments for the reservation.
     */
    public function payments()
    {
        return $this->hasMany('App\Models\Payment');
    }

    /**
     * Get all of the charges for the reservation.
     */
    public function charges()
    {
        return $this->hasMany('App\Models\Charge');
    }

    /**
     * Get all of the extra beds for the reservation.
     */
    public function extraBeds()
    {
        return $this->hasMany('App\Models\ExtraBed');
    }

    /**
     * Get all of the extra beds for the reservation.
     */
    public function children()
    {
        return $this->hasMany('App\Models\Child');
    }

    /**
     * Get all of the items for the reservation.
     */
    public function items()
    {
        return $this->hasMany('App\Models\Item');
    }

    /**
     * Get the cancellation policy clone associated with the reservation.
     */
    public function cancellationPolicyClone()
    {
        return $this->belongsTo('App\Models\CancellationPolicyClone');
    }

    /**
     * Get cancellation for the reservation.
     */
    public function cancellation()
    {
        return $this->hasOne('App\Models\Cancellation');
    }

    /**
     * Get reservation request for the reservation.
     */
    public function resReq()
    {
        return $this->belongsTo('App\Models\ResReq', 'res_req_id');
    }

    /**
     * Get reservation payment method for the reservation.
     */
    public function reservationPaymentMethod()
    {
        return $this->hasOne('App\Models\ReservationPaymentMethod');
    }
}
