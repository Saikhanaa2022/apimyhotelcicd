<?php

namespace App\Observers;

use App\Models\Reservation;

class ReservationObserver
{
    /**
     * Handle the reservation "created" event.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function created(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "updated" event.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function updated(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "deleted" event.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function deleted(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "restored" event.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function restored(Reservation $reservation)
    {
        //
    }

    /**
     * Handle the reservation "force deleted" event.
     *
     * @param  \App\Models\Reservation  $reservation
     * @return void
     */
    public function forceDeleted(Reservation $reservation)
    {
        //
    }
}
