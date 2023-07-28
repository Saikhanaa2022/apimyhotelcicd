<?php

namespace App\Observers;

use App\Models\Guest;

class GuestObserver
{
    /**
     * Handle the guest "created" event.
     *
     * @param  \App\Models\Guest  $guest
     * @return void
     */
    public function created(Guest $guest)
    {
        //
    }

    /**
     * Handle the guest "updated" event.
     *
     * @param  \App\Models\Guest  $guest
     * @return void
     */
    public function updated(Guest $guest)
    {
        //
    }

    /**
     * Handle the guest "deleted" event.
     *
     * @param  \App\Models\Guest  $guest
     * @return void
     */
    public function deleted(Guest $guest)
    {
        //
    }

    /**
     * Handle the guest "restored" event.
     *
     * @param  \App\Models\Guest  $guest
     * @return void
     */
    public function restored(Guest $guest)
    {
        //
    }

    /**
     * Handle the guest "force deleted" event.
     *
     * @param  \App\Models\Guest  $guest
     * @return void
     */
    public function forceDeleted(Guest $guest)
    {
        //
    }
}
