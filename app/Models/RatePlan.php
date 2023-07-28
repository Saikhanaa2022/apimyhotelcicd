<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use App\Traits\HasTranslation;

class RatePlan extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rate_plans';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_daily' => 'boolean',
        'is_ota' => 'boolean',
        'is_online_book' => 'boolean',
        'non_ref' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_ota', 'is_online_book', 'is_daily', 'room_type_id', 'non_ref',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'type',
        'translate'
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\RatePlanTranslation', 'translation_id', 'id');
	}

    /**
     * Get available daily rates in range.
     *
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @return Illuminate\Support\Collection
     */
    public function getDailyRates($checkIn, $checkOut)
    {
        return $this->dailyRates()
            ->availableIn($checkIn, $checkOut)
            ->get();
    }

    /**
     * Get available daily rate in range.
     *
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @return Illuminate\Support\Collection
     */
    public function getDailyRate($checkIn, $checkOut)
    {
        return $this->dailyRates()
            ->availableIn($checkIn, $checkOut)
            ->select(['id', 'date', 'value'])
            ->first();
    }

    /**
     * Get available rates in range.
     *
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @return Illuminate\Support\Collection
     */
    public function getRates($checkIn, $checkOut)
    {
        $interval = $this->intervals()
            ->availableIn($checkIn, $checkOut)
            ->first();

        if (!$interval) {
            return collect();
        }

        $rates = $interval->getRates($checkIn, $checkOut);

        return $rates;
    }

    /**
     * Scope a query to only available rate plans.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableIn($query, $checkIn, $checkOut)
    {
        // Stay nights
        $nights = stayNights($checkIn, $checkOut);

        return $query
            ->where(function ($query) use ($checkIn, $checkOut, $nights) {
                $query
                    ->where('is_daily', true)
                    ->whereHas('dailyRates', function ($query) use ($checkIn, $checkOut) {
                        $query->availableIn($checkIn, $checkOut);
                    }, '=', $nights);
            });
            // ->orWhere(function ($query) use ($checkIn, $checkOut) {
            //     $query
            //         ->where('is_daily', false)
            //         ->whereHas('intervals', function ($query) use ($checkIn, $checkOut) {
            //             $query->availableIn($checkIn, $checkOut);
            //         });
            // });
    }

    /**
     * Get the rate plan's type.
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return $this->is_daily ? 'daily' : 'intensive';
    }

    /**
     * Set the rate plan's is daily attribute.
     *
     * @param  string  $value
     * @return void
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['is_daily'] = $value === 'daily';
    }

    /**
     * Get the rate plan's room type.
     */
    public function roomType()
    {
        return $this->belongsTo('App\Models\RoomType');
    }

    /**
     * Get all of the partners for the rate plan.
     */
    public function partners()
    {
        return $this->belongsToMany('App\Models\Partner');
    }

    /**
     * Get all of the partners for the rate plan.
     */
    public function meals()
    {
        return $this->belongsToMany('App\Models\Meal');
    }

    /**
     * Get all of the items for the rate plan.
     */
    public function ratePlanItems()
    {
        return $this->hasMany('App\Models\RatePlanItem');
    }

    /**
     * Get all of the daily rates for the rate plan.
     */
    public function dailyRates()
    {
        return $this->hasMany('App\Models\DailyRate');
    }

    /**
     * Get all of the rates for the rate plan.
     */
    public function intervals()
    {
        return $this->hasMany('App\Models\Interval');
    }

    /**
     * Get all of the clones for the payment method.
     */
    public function ratePlanClones()
    {
        return $this->hasMany('App\Models\RatePlanClone');
    }

    /**
     * Get all of the occupancy rate plans.
     */
    public function occupancyRatePlans()
    {
        return $this->hasMany('App\Models\OccupancyRatePlan');
    }
}
