<?php

namespace App\Observers;

use App\Models\SourceClone;

class SourceCloneObserver
{
    /**
     * Handle the source clone "created" event.
     *
     * @param  \App\Models\SourceClone  $sourceClone
     * @return void
     */
    public function created(SourceClone $sourceClone)
    {
        //
    }

    /**
     * Handle the source clone "updated" event.
     *
     * @param  \App\Models\SourceClone  $sourceClone
     * @return void
     */
    public function updated(SourceClone $sourceClone)
    {
        //
    }

    /**
     * Handle the source clone "deleted" event.
     *
     * @param  \App\Models\SourceClone  $sourceClone
     * @return void
     */
    public function deleted(SourceClone $sourceClone)
    {
        //
    }

    /**
     * Handle the source clone "restored" event.
     *
     * @param  \App\Models\SourceClone  $sourceClone
     * @return void
     */
    public function restored(SourceClone $sourceClone)
    {
        //
    }

    /**
     * Handle the source clone "force deleted" event.
     *
     * @param  \App\Models\SourceClone  $sourceClone
     * @return void
     */
    public function forceDeleted(SourceClone $sourceClone)
    {
        //
    }
}
