<?php

namespace App\Observers;

use App\Models\GuestClone;

class GuestCloneObserver
{
    /**
     * Handle the guest clone "created" event.
     *
     * @param  \App\Models\GuestClone  $guestClone
     * @return void
     */
    public function created(GuestClone $guestClone)
    {
        //
    }

    /**
     * Handle the guest clone "updated" event.
     *
     * @param  \App\Models\GuestClone  $guestClone
     * @return void
     */
    public function updated(GuestClone $guestClone)
    {
        //
    }

    /**
     * Handle the guest clone "deleted" event.
     *
     * @param  \App\Models\GuestClone  $guestClone
     * @return void
     */
    public function deleted(GuestClone $guestClone)
    {
        //
    }

    /**
     * Handle the guest clone "restored" event.
     *
     * @param  \App\Models\GuestClone  $guestClone
     * @return void
     */
    public function restored(GuestClone $guestClone)
    {
        //
    }

    /**
     * Handle the guest clone "force deleted" event.
     *
     * @param  \App\Models\GuestClone  $guestClone
     * @return void
     */
    public function forceDeleted(GuestClone $guestClone)
    {
        //
    }
}
