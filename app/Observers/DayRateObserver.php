<?php

namespace App\Observers;

use App\Models\DayRate;

class DayRateObserver
{
    /**
     * Handle the day rate "created" event.
     *
     * @param  \App\Models\DayRate  $dayRate
     * @return void
     */
    public function created(DayRate $dayRate)
    {
        //
    }

    /**
     * Handle the day rate "updated" event.
     *
     * @param  \App\Models\DayRate  $dayRate
     * @return void
     */
    public function updated(DayRate $dayRate)
    {
        //
    }

    /**
     * Handle the day rate "deleted" event.
     *
     * @param  \App\Models\DayRate  $dayRate
     * @return void
     */
    public function deleted(DayRate $dayRate)
    {
        //
    }

    /**
     * Handle the day rate "restored" event.
     *
     * @param  \App\Models\DayRate  $dayRate
     * @return void
     */
    public function restored(DayRate $dayRate)
    {
        //
    }

    /**
     * Handle the day rate "force deleted" event.
     *
     * @param  \App\Models\DayRate  $dayRate
     * @return void
     */
    public function forceDeleted(DayRate $dayRate)
    {
        //
    }
}
