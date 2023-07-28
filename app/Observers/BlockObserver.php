<?php

namespace App\Observers;

use App\Models\Block;

class BlockObserver
{
    /**
     * Handle the block "created" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function created(Block $block)
    {
        //
    }

    /**
     * Handle the block "updated" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function updated(Block $block)
    {
        //
    }

    /**
     * Handle the block "deleted" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function deleted(Block $block)
    {
        //
    }

    /**
     * Handle the block "restored" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function restored(Block $block)
    {
        //
    }

    /**
     * Handle the block "force deleted" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function forceDeleted(Block $block)
    {
        //
    }
}
