<?php

namespace App\Observers;

use App\Models\ServiceCategory;

class ServiceCategoryObserver
{
    /**
     * Handle the service category "created" event.
     *
     * @param  \App\Models\ServiceCategory  $serviceCategory
     * @return void
     */
    public function created(ServiceCategory $serviceCategory)
    {
        //
    }

    /**
     * Handle the service category "updated" event.
     *
     * @param  \App\Models\ServiceCategory  $serviceCategory
     * @return void
     */
    public function updated(ServiceCategory $serviceCategory)
    {
        //
    }

    /**
     * Handle the service category "deleted" event.
     *
     * @param  \App\Models\ServiceCategory  $serviceCategory
     * @return void
     */
    public function deleted(ServiceCategory $serviceCategory)
    {
        //
    }

    /**
     * Handle the service category "restored" event.
     *
     * @param  \App\Models\ServiceCategory  $serviceCategory
     * @return void
     */
    public function restored(ServiceCategory $serviceCategory)
    {
        //
    }

    /**
     * Handle the service category "force deleted" event.
     *
     * @param  \App\Models\ServiceCategory  $serviceCategory
     * @return void
     */
    public function forceDeleted(ServiceCategory $serviceCategory)
    {
        //
    }
}
