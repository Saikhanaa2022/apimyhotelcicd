<?php

namespace App\Observers;

use App\Models\RoomType;

class RoomTypeObserver
{
    /**
     * Handle the room type "created" event.
     *
     * @param  \App\Models\RoomType  $roomType
     * @return void
     */
    public function created(RoomType $roomType)
    {
        //
    }

    /**
     * Handle the room type "updated" event.
     *
     * @param  \App\Models\RoomType  $roomType
     * @return void
     */
    public function updated(RoomType $roomType)
    {
        //
    }

    /**
     * Handle the room type "deleted" event.
     *
     * @param  \App\Models\RoomType  $roomType
     * @return void
     */
    public function deleted(RoomType $roomType)
    {
        //
    }

    /**
     * Handle the room type "restored" event.
     *
     * @param  \App\Models\RoomType  $roomType
     * @return void
     */
    public function restored(RoomType $roomType)
    {
        //
    }

    /**
     * Handle the room type "force deleted" event.
     *
     * @param  \App\Models\RoomType  $roomType
     * @return void
     */
    public function forceDeleted(RoomType $roomType)
    {
        //
    }
}
