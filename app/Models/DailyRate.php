<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class DailyRate extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'daily_rates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date', 'value', 'min_los', 'max_los', 'rate_plan_id',
    ];

    /**
     * Scope a query to only available daily rates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param  string  $checkIn
     * @param  string  $checkOut
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableIn($query, $checkIn, $checkOut)
    {
        // Stay nights
        $nights = stayNights($checkIn, $checkOut);

        // Get all dates
        $dates = getDatesFromRange(Carbon::parse($checkIn)->format('Y-m-d'), Carbon::parse(Carbon::parse($checkOut)->format('Y-m-d'))->subDays(1)->format('Y-m-d'));

        return $query
            ->whereIn('date', $dates)
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
     * Get the rate plan associated with the daily rate.
     */
    public function ratePlan()
    {
        return $this->belongsTo('App\Models\RatePlan');
    }
}
