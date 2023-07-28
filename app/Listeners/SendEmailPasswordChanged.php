<?php

namespace App\Listeners;

use App\Events\PasswordChanged;
// use App\Notifications\PasswordChanged as PasswordChangedNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\SendEmail;

class SendEmailPasswordChanged
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
    public function handle(PasswordChanged $event)
    {
        // $event->user->notify(new PasswordChangedNotification($event->user));
        $emailData = [
            'toEmail' => $event->user->email,
            'emailType' => 'passwordChanged'
        ];

        SendEmail::dispatch($emailData, $event->user);
    }
}
