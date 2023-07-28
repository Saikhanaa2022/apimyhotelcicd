<?php

namespace App\Observers;

use App\Models\PaymentMethod;

class PaymentMethodObserver
{
    /**
     * Handle the payment method "created" event.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return void
     */
    public function created(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Handle the payment method "updated" event.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return void
     */
    public function updated(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Handle the payment method "deleted" event.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return void
     */
    public function deleted(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Handle the payment method "restored" event.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return void
     */
    public function restored(PaymentMethod $paymentMethod)
    {
        //
    }

    /**
     * Handle the payment method "force deleted" event.
     *
     * @param  \App\Models\PaymentMethod  $paymentMethod
     * @return void
     */
    public function forceDeleted(PaymentMethod $paymentMethod)
    {
        //
    }
}
