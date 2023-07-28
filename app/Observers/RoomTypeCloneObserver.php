<?php

namespace App\Observers;

use App\Models\RoomTypeClone;

class RoomTypeCloneObserver
{
    /**
     * Handle the room type clone "created" event.
     *
     * @param  \App\Models\RoomTypeClone  $roomTypeClone
     * @return void
     */
    public function created(RoomTypeClone $roomTypeClone)
    {
        //
    }

    /**
     * Handle the room type clone "updated" event.
     *
     * @param  \App\Models\RoomTypeClone  $roomTypeClone
     * @return void
     */
    public function updated(RoomTypeClone $roomTypeClone)
    {
        //
    }

    /**
     * Handle the room type clone "deleted" event.
     *
     * @param  \App\Models\RoomTypeClone  $roomTypeClone
     * @return void
     */
    public function deleted(RoomTypeClone $roomTypeClone)
    {
        //
    }

    /**
     * Handle the room type clone "restored" event.
     *
     * @param  \App\Models\RoomTypeClone  $roomTypeClone
     * @return void
     */
    public function restored(RoomTypeClone $roomTypeClone)
    {
        //
    }

    /**
     * Handle the room type clone "force deleted" event.
     *
     * @param  \App\Models\RoomTypeClone  $roomTypeClone
     * @return void
     */
    public function forceDeleted(RoomTypeClone $roomTypeClone)
    {
        //
    }
}
