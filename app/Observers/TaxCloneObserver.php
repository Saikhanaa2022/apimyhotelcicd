<?php

namespace App\Observers;

use App\Models\TaxClone;

class TaxCloneObserver
{
    /**
     * Handle the tax clone "created" event.
     *
     * @param  \App\Models\TaxClone  $taxClone
     * @return void
     */
    public function created(TaxClone $taxClone)
    {
        //
    }

    /**
     * Handle the tax clone "updated" event.
     *
     * @param  \App\Models\TaxClone  $taxClone
     * @return void
     */
    public function updated(TaxClone $taxClone)
    {
        //
    }

    /**
     * Handle the tax clone "deleted" event.
     *
     * @param  \App\Models\TaxClone  $taxClone
     * @return void
     */
    public function deleted(TaxClone $taxClone)
    {
        //
    }

    /**
     * Handle the tax clone "restored" event.
     *
     * @param  \App\Models\TaxClone  $taxClone
     * @return void
     */
    public function restored(TaxClone $taxClone)
    {
        //
    }

    /**
     * Handle the tax clone "force deleted" event.
     *
     * @param  \App\Models\TaxClone  $taxClone
     * @return void
     */
    public function forceDeleted(TaxClone $taxClone)
    {
        //
    }
}
