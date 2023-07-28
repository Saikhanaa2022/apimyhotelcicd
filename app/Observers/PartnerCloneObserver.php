<?php

namespace App\Observers;

use App\Models\PartnerClone;

class PartnerCloneObserver
{
    /**
     * Handle the partner clone "created" event.
     *
     * @param  \App\Models\PartnerClone  $partnerClone
     * @return void
     */
    public function created(PartnerClone $partnerClone)
    {
        //
    }

    /**
     * Handle the partner clone "updated" event.
     *
     * @param  \App\Models\PartnerClone  $partnerClone
     * @return void
     */
    public function updated(PartnerClone $partnerClone)
    {
        //
    }

    /**
     * Handle the partner clone "deleted" event.
     *
     * @param  \App\Models\PartnerClone  $partnerClone
     * @return void
     */
    public function deleted(PartnerClone $partnerClone)
    {
        //
    }

    /**
     * Handle the partner clone "restored" event.
     *
     * @param  \App\Models\PartnerClone  $partnerClone
     * @return void
     */
    public function restored(PartnerClone $partnerClone)
    {
        //
    }

    /**
     * Handle the partner clone "force deleted" event.
     *
     * @param  \App\Models\PartnerClone  $partnerClone
     * @return void
     */
    public function forceDeleted(PartnerClone $partnerClone)
    {
        //
    }
}
