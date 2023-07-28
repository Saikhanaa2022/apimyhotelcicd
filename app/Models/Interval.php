<?php

namespace App\Models;

use App\Models\DailyRate;
use Illuminate\Support\Carbon;

class Interval extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'intervals';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'monday_rate', 'tuesday_rate', 'wednesday_rate', 'thursday_rate', 'friday_rate', 'saturday_rate', 'sunday_rate',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'start_date', 'end_date', 'min_los', 'max_los', 'rate_plan_id',
    ];

    /**
     * Get available rates in range.
     *
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @return Illuminate\Support\Collection
     */
    public function getRates($checkIn, $checkOut)
    {
        $rates = [];
        // All dates
        $dates = getDatesFromRange($checkIn, Carbon::parse($checkOut)->subDays(1)->format('Y-m-d'));

        foreach ($dates as $date) {
            $rate = $this->rates()
                ->where('day_of_week', strtolower(dayOfWeek($date)))
                ->first();

            if ($rate) {
                $dailyRate = new DailyRate([
                    'date' => $date,
                    'value' => $rate->value,
                ]);

                array_push($rates, $dailyRate);
            }
        }

        return collect($rates);
    }

    /**
     * Scope a query to only available daily rates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableIn($query, $checkIn, $checkOut)
    {
        // Stay nights
        $nights = stayNights($checkIn, $checkOut);

        // All dates
        $dates = getDatesFromRange($checkIn, Carbon::parse($checkOut)->subDays(1)->format('Y-m-d'));

        // Get all days of week
        $days = collect($dates)
            ->map(function ($date) {
                return strtolower(dayOfWeek($date));
            })
            ->unique()
            ->values()
            ->all();

        return $query
            ->where([
                ['start_date', '<=', $checkIn],
                ['end_date', '>=', Carbon::parse($checkOut)->subDays(1)->format('Y-m-d')],
            ])
            ->whereHas('rates', function ($query) use ($days) {
                $query->whereIn('day_of_week', $days);
            }, '=', count($days))
            ->where(function ($query) use ($nights) {
                $query->whereNull('min_los')
                    ->orWhere('min_los', '<=', $nights);
            })
            ->where(function ($query) use ($nights) {
                $query->whereNull('max_los')
                    ->orWhere('max_los', '>=', $nights);
            });
    }

    /**
     * Sync rates in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function syncRates(\Illuminate\Http\Request $request)
    {
        $days = [
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
        ];

        foreach ($days as $day) {
            if ($request->filled($day . 'Rate')) {
                $this->rates()
                    ->updateOrCreate(
                        ['day_of_week' => $day],
                        ['value' => $request->input($day . 'Rate')]
                    );
            } else {
                $this->rates()
                    ->where('day_of_week', $day)
                    ->delete();
            }
        }
    }

    /**
     * Get the rate plan associated with the interval.
     */
    public function ratePlan()
    {
        return $this->belongsTo('App\Models\RatePlan');
    }

    /**
     * Get the interval's rates.
     */
    public function rates()
    {
        return $this->hasMany('App\Models\Rate');
    }

    /**
     * Get the interval's monday rate.
     *
     * @return string
     */
    public function getMondayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'monday')
            ->first();

        return $rate->value ?? null;
    }

    /**
     * Get the interval's tuesday rate.
     *
     * @return string
     */
    public function getTuesdayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'tuesday')
            ->first();

        return $rate->value ?? null;
    }

    /**
     * Get the interval's wednesday rate.
     *
     * @return string
     */
    public function getWednesdayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'wednesday')
            ->first();

        return $rate->value ?? null;
    }

    /**
     * Get the interval's thursday rate.
     *
     * @return string
     */
    public function getThursdayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'thursday')
            ->first();

        return $rate->value ?? null;
    }

    /**
     * Get the interval's friday rate.
     *
     * @return string
     */
    public function getFridayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'friday')
            ->first();

        return $rate->value ?? null;
    }

    /**
     * Get the interval's saturday rate.
     *
     * @return string
     */
    public function getSaturdayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'saturday')
            ->first();

        return $rate->value ?? null;
    }

    /**
     * Get the interval's sunday rate.
     *
     * @return string
     */
    public function getSundayRateAttribute()
    {
        $rate = $this->rates()
            ->where('day_of_week', 'sunday')
            ->first();

        return $rate->value ?? null;
    }
}
