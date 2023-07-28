<?php

namespace App\Observers;

use App\Models\Rate;

class RateObserver
{
    /**
     * Handle the rate "created" event.
     *
     * @param  \App\Models\Rate  $rate
     * @return void
     */
    public function created(Rate $rate)
    {
        //
    }

    /**
     * Handle the rate "updated" event.
     *
     * @param  \App\Models\Rate  $rate
     * @return void
     */
    public function updated(Rate $rate)
    {
        //
    }

    /**
     * Handle the rate "deleted" event.
     *
     * @param  \App\Models\Rate  $rate
     * @return void
     */
    public function deleted(Rate $rate)
    {
        //
    }

    /**
     * Handle the rate "restored" event.
     *
     * @param  \App\Models\Rate  $rate
     * @return void
     */
    public function restored(Rate $rate)
    {
        //
    }

    /**
     * Handle the rate "force deleted" event.
     *
     * @param  \App\Models\Rate  $rate
     * @return void
     */
    public function forceDeleted(Rate $rate)
    {
        //
    }
}
