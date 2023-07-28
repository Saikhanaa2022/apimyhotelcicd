<?php

namespace App\Observers;

use App\Models\Source;

class SourceObserver
{
    /**
     * Handle the source "created" event.
     *
     * @param  \App\Models\Source  $source
     * @return void
     */
    public function created(Source $source)
    {
        //
    }

    /**
     * Handle the source "updated" event.
     *
     * @param  \App\Models\Source  $source
     * @return void
     */
    public function updated(Source $source)
    {
        //
    }

    /**
     * Handle the source "deleted" event.
     *
     * @param  \App\Models\Source  $source
     * @return void
     */
    public function deleted(Source $source)
    {
        //
    }

    /**
     * Handle the source "restored" event.
     *
     * @param  \App\Models\Source  $source
     * @return void
     */
    public function restored(Source $source)
    {
        //
    }

    /**
     * Handle the source "force deleted" event.
     *
     * @param  \App\Models\Source  $source
     * @return void
     */
    public function forceDeleted(Source $source)
    {
        //
    }
}
