<?php

namespace App\Observers;

use App\Models\RatePlanClone;

class RatePlanCloneObserver
{
    /**
     * Handle the rate plan clone "created" event.
     *
     * @param  \App\Models\RatePlanClone  $ratePlanClone
     * @return void
     */
    public function created(RatePlanClone $ratePlanClone)
    {
        //
    }

    /**
     * Handle the rate plan clone "updated" event.
     *
     * @param  \App\Models\RatePlanClone  $ratePlanClone
     * @return void
     */
    public function updated(RatePlanClone $ratePlanClone)
    {
        //
    }

    /**
     * Handle the rate plan clone "deleted" event.
     *
     * @param  \App\Models\RatePlanClone  $ratePlanClone
     * @return void
     */
    public function deleted(RatePlanClone $ratePlanClone)
    {
        //
    }

    /**
     * Handle the rate plan clone "restored" event.
     *
     * @param  \App\Models\RatePlanClone  $ratePlanClone
     * @return void
     */
    public function restored(RatePlanClone $ratePlanClone)
    {
        //
    }

    /**
     * Handle the rate plan clone "force deleted" event.
     *
     * @param  \App\Models\RatePlanClone  $ratePlanClone
     * @return void
     */
    public function forceDeleted(RatePlanClone $ratePlanClone)
    {
        //
    }
}
