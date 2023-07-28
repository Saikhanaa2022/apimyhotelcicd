<?php

namespace App\Observers;

use App\Models\Tax;

class TaxObserver
{
    /**
     * Handle the tax "created" event.
     *
     * @param  \App\Models\Tax  $tax
     * @return void
     */
    public function created(Tax $tax)
    {
        //
    }

    /**
     * Handle the tax "updated" event.
     *
     * @param  \App\Models\Tax  $tax
     * @return void
     */
    public function updated(Tax $tax)
    {
        //
    }

    /**
     * Handle the tax "deleted" event.
     *
     * @param  \App\Models\Tax  $tax
     * @return void
     */
    public function deleted(Tax $tax)
    {
        //
    }

    /**
     * Handle the tax "restored" event.
     *
     * @param  \App\Models\Tax  $tax
     * @return void
     */
    public function restored(Tax $tax)
    {
        //
    }

    /**
     * Handle the tax "force deleted" event.
     *
     * @param  \App\Models\Tax  $tax
     * @return void
     */
    public function forceDeleted(Tax $tax)
    {
        //
    }
}
