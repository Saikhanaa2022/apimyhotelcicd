<?php

namespace App\Observers;

use App\Models\CurrencyClone;

class CurrencyCloneObserver
{
    /**
     * Handle the currency clone "created" event.
     *
     * @param  \App\Models\CurrencyClone  $currencyClone
     * @return void
     */
    public function created(CurrencyClone $currencyClone)
    {
        //
    }

    /**
     * Handle the currency clone "updated" event.
     *
     * @param  \App\Models\CurrencyClone  $currencyClone
     * @return void
     */
    public function updated(CurrencyClone $currencyClone)
    {
        //
    }

    /**
     * Handle the currency clone "deleted" event.
     *
     * @param  \App\Models\CurrencyClone  $currencyClone
     * @return void
     */
    public function deleted(CurrencyClone $currencyClone)
    {
        //
    }

    /**
     * Handle the currency clone "restored" event.
     *
     * @param  \App\Models\CurrencyClone  $currencyClone
     * @return void
     */
    public function restored(CurrencyClone $currencyClone)
    {
        //
    }

    /**
     * Handle the currency clone "force deleted" event.
     *
     * @param  \App\Models\CurrencyClone  $currencyClone
     * @return void
     */
    public function forceDeleted(CurrencyClone $currencyClone)
    {
        //
    }
}
