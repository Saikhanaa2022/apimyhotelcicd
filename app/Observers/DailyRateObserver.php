<?php

namespace App\Observers;

use App\Models\DailyRate;

class DailyRateObserver
{
    /**
     * Handle the daily rate "created" event.
     *
     * @param  \App\Models\DailyRate  $dailyRate
     * @return void
     */
    public function created(DailyRate $dailyRate)
    {
        //
    }

    /**
     * Handle the daily rate "updated" event.
     *
     * @param  \App\Models\DailyRate  $dailyRate
     * @return void
     */
    public function updated(DailyRate $dailyRate)
    {
        //
    }

    /**
     * Handle the daily rate "deleted" event.
     *
     * @param  \App\Models\DailyRate  $dailyRate
     * @return void
     */
    public function deleted(DailyRate $dailyRate)
    {
        //
    }

    /**
     * Handle the daily rate "restored" event.
     *
     * @param  \App\Models\DailyRate  $dailyRate
     * @return void
     */
    public function restored(DailyRate $dailyRate)
    {
        //
    }

    /**
     * Handle the daily rate "force deleted" event.
     *
     * @param  \App\Models\DailyRate  $dailyRate
     * @return void
     */
    public function forceDeleted(DailyRate $dailyRate)
    {
        //
    }
}
