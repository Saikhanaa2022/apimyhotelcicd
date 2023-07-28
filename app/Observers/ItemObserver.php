<?php

namespace App\Observers;

use App\Models\Item;

class ItemObserver
{
    /**
     * Handle the item "created" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function created(Item $item)
    {
        //
    }

    /**
     * Handle the item "updated" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function updated(Item $item)
    {
        //
    }

    /**
     * Handle the item "deleted" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function deleted(Item $item)
    {
        //
    }

    /**
     * Handle the item "restored" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function restored(Item $item)
    {
        //
    }

    /**
     * Handle the item "force deleted" event.
     *
     * @param  \App\Models\Item  $item
     * @return void
     */
    public function forceDeleted(Item $item)
    {
        //
    }
}
