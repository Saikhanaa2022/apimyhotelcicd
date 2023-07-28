<?php

namespace App\Listeners;

// use App\Models\User;
use App\Events\EmailInvoice;
// use App\Notifications\EmailInvoice as EmailInvoiceNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\SendEmail;

class SendEmailInvoice
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(EmailInvoice $event)
    {
        // $user = new User;
        // $user->email = $event->email;

        // $user->notify(new EmailInvoiceNotification($event->invoice));

        $emailData = [
            'toEmail' => $event->email,
            'emailType' => 'invoiceCreated'
        ];

        SendEmail::dispatch($emailData, $event->invoice)->onConnection('sync');
    }
}
