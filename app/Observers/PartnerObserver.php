<?php

namespace App\Observers;

use App\Models\Partner;

class PartnerObserver
{
    /**
     * Handle the partner "created" event.
     *
     * @param  \App\Models\Partner  $partner
     * @return void
     */
    public function created(Partner $partner)
    {
        //
    }

    /**
     * Handle the partner "updated" event.
     *
     * @param  \App\Models\Partner  $partner
     * @return void
     */
    public function updated(Partner $partner)
    {
        //
    }

    /**
     * Handle the partner "deleted" event.
     *
     * @param  \App\Models\Partner  $partner
     * @return void
     */
    public function deleted(Partner $partner)
    {
        //
    }

    /**
     * Handle the partner "restored" event.
     *
     * @param  \App\Models\Partner  $partner
     * @return void
     */
    public function restored(Partner $partner)
    {
        //
    }

    /**
     * Handle the partner "force deleted" event.
     *
     * @param  \App\Models\Partner  $partner
     * @return void
     */
    public function forceDeleted(Partner $partner)
    {
        //
    }
}
