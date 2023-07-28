<?php

namespace App\Observers;

use App\Models\ServiceClone;

class ServiceCloneObserver
{
    /**
     * Handle the service clone "created" event.
     *
     * @param  \App\Models\ServiceClone  $serviceClone
     * @return void
     */
    public function created(ServiceClone $serviceClone)
    {
        //
    }

    /**
     * Handle the service clone "updated" event.
     *
     * @param  \App\Models\ServiceClone  $serviceClone
     * @return void
     */
    public function updated(ServiceClone $serviceClone)
    {
        //
    }

    /**
     * Handle the service clone "deleted" event.
     *
     * @param  \App\Models\ServiceClone  $serviceClone
     * @return void
     */
    public function deleted(ServiceClone $serviceClone)
    {
        //
    }

    /**
     * Handle the service clone "restored" event.
     *
     * @param  \App\Models\ServiceClone  $serviceClone
     * @return void
     */
    public function restored(ServiceClone $serviceClone)
    {
        //
    }

    /**
     * Handle the service clone "force deleted" event.
     *
     * @param  \App\Models\ServiceClone  $serviceClone
     * @return void
     */
    public function forceDeleted(ServiceClone $serviceClone)
    {
        //
    }
}
