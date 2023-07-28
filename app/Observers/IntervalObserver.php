<?php

namespace App\Observers;

use App\Models\Interval;

class IntervalObserver
{
    /**
     * Handle the interval "created" event.
     *
     * @param  \App\Models\Interval  $interval
     * @return void
     */
    public function created(Interval $interval)
    {
        //
    }

    /**
     * Handle the interval "updated" event.
     *
     * @param  \App\Models\Interval  $interval
     * @return void
     */
    public function updated(Interval $interval)
    {
        //
    }

    /**
     * Handle the interval "deleted" event.
     *
     * @param  \App\Models\Interval  $interval
     * @return void
     */
    public function deleted(Interval $interval)
    {
        //
    }

    /**
     * Handle the interval "restored" event.
     *
     * @param  \App\Models\Interval  $interval
     * @return void
     */
    public function restored(Interval $interval)
    {
        //
    }

    /**
     * Handle the interval "force deleted" event.
     *
     * @param  \App\Models\Interval  $interval
     * @return void
     */
    public function forceDeleted(Interval $interval)
    {
        //
    }
}
