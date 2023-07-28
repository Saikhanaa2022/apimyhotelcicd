<?php

namespace App\Observers;

use App\Models\ServiceCategoryClone;

class ServiceCategoryCloneObserver
{
    /**
     * Handle the service category clone "created" event.
     *
     * @param  \App\Models\ServiceCategoryClone  $serviceCategoryClone
     * @return void
     */
    public function created(ServiceCategoryClone $serviceCategoryClone)
    {
        //
    }

    /**
     * Handle the service category clone "updated" event.
     *
     * @param  \App\Models\ServiceCategoryClone  $serviceCategoryClone
     * @return void
     */
    public function updated(ServiceCategoryClone $serviceCategoryClone)
    {
        //
    }

    /**
     * Handle the service category clone "deleted" event.
     *
     * @param  \App\Models\ServiceCategoryClone  $serviceCategoryClone
     * @return void
     */
    public function deleted(ServiceCategoryClone $serviceCategoryClone)
    {
        //
    }

    /**
     * Handle the service category clone "restored" event.
     *
     * @param  \App\Models\ServiceCategoryClone  $serviceCategoryClone
     * @return void
     */
    public function restored(ServiceCategoryClone $serviceCategoryClone)
    {
        //
    }

    /**
     * Handle the service category clone "force deleted" event.
     *
     * @param  \App\Models\ServiceCategoryClone  $serviceCategoryClone
     * @return void
     */
    public function forceDeleted(ServiceCategoryClone $serviceCategoryClone)
    {
        //
    }
}
