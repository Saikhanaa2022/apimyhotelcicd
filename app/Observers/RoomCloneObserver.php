<?php

namespace App\Observers;

use App\Models\RoomClone;

class RoomCloneObserver
{
    /**
     * Handle the room clone "created" event.
     *
     * @param  \App\Models\RoomClone  $roomClone
     * @return void
     */
    public function created(RoomClone $roomClone)
    {
        //
    }

    /**
     * Handle the room clone "updated" event.
     *
     * @param  \App\Models\RoomClone  $roomClone
     * @return void
     */
    public function updated(RoomClone $roomClone)
    {
        //
    }

    /**
     * Handle the room clone "deleted" event.
     *
     * @param  \App\Models\RoomClone  $roomClone
     * @return void
     */
    public function deleted(RoomClone $roomClone)
    {
        //
    }

    /**
     * Handle the room clone "restored" event.
     *
     * @param  \App\Models\RoomClone  $roomClone
     * @return void
     */
    public function restored(RoomClone $roomClone)
    {
        //
    }

    /**
     * Handle the room clone "force deleted" event.
     *
     * @param  \App\Models\RoomClone  $roomClone
     * @return void
     */
    public function forceDeleted(RoomClone $roomClone)
    {
        //
    }
}
