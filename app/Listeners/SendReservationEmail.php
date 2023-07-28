<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
// use App\Models\User;
// use App\Notifications\SendReservationEmail as SendReservationEmailNotification;
use App\Jobs\SendEmail;

class SendReservationEmail
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
    public function handle($event)
    {
        // $user = new User;
        // $user->email = $event->email;

        // $user->notify(new SendReservationEmailNotification($event->dataId, $event->emailType, $event->isGroup));

        $emailData = [
            'toEmail' => $event->email,
            'emailType' => 'guestReservation',
            'emailDocType' => $event->emailType,
            'dataId' => $event->dataId,
            'isGroup' => $event->isGroup,
        ];

        SendEmail::dispatch($emailData)->onConnection('sync');
    }
}
