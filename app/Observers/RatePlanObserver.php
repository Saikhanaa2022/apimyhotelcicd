<?php

namespace App\Observers;

use App\Models\RatePlan;

class RatePlanObserver
{
    /**
     * Handle the rate plan "created" event.
     *
     * @param  \App\Models\RatePlan  $ratePlan
     * @return void
     */
    public function created(RatePlan $ratePlan)
    {
        //
    }

    /**
     * Handle the rate plan "updated" event.
     *
     * @param  \App\Models\RatePlan  $ratePlan
     * @return void
     */
    public function updated(RatePlan $ratePlan)
    {
        //
    }

    /**
     * Handle the rate plan "deleted" event.
     *
     * @param  \App\Models\RatePlan  $ratePlan
     * @return void
     */
    public function deleted(RatePlan $ratePlan)
    {
        //
    }

    /**
     * Handle the rate plan "restored" event.
     *
     * @param  \App\Models\RatePlan  $ratePlan
     * @return void
     */
    public function restored(RatePlan $ratePlan)
    {
        //
    }

    /**
     * Handle the rate plan "force deleted" event.
     *
     * @param  \App\Models\RatePlan  $ratePlan
     * @return void
     */
    public function forceDeleted(RatePlan $ratePlan)
    {
        //
    }
}
