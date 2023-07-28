<?php

namespace App\Observers;

use App\Models\PaymentMethodClone;

class PaymentMethodCloneObserver
{
    /**
     * Handle the payment method clone "created" event.
     *
     * @param  \App\Models\PaymentMethodClone  $paymentMethodClone
     * @return void
     */
    public function created(PaymentMethodClone $paymentMethodClone)
    {
        //
    }

    /**
     * Handle the payment method clone "updated" event.
     *
     * @param  \App\Models\PaymentMethodClone  $paymentMethodClone
     * @return void
     */
    public function updated(PaymentMethodClone $paymentMethodClone)
    {
        //
    }

    /**
     * Handle the payment method clone "deleted" event.
     *
     * @param  \App\Models\PaymentMethodClone  $paymentMethodClone
     * @return void
     */
    public function deleted(PaymentMethodClone $paymentMethodClone)
    {
        //
    }

    /**
     * Handle the payment method clone "restored" event.
     *
     * @param  \App\Models\PaymentMethodClone  $paymentMethodClone
     * @return void
     */
    public function restored(PaymentMethodClone $paymentMethodClone)
    {
        //
    }

    /**
     * Handle the payment method clone "force deleted" event.
     *
     * @param  \App\Models\PaymentMethodClone  $paymentMethodClone
     * @return void
     */
    public function forceDeleted(PaymentMethodClone $paymentMethodClone)
    {
        //
    }
}
