<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Notifications\ReservationCreated as ReservationCreatedNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\SendEmail;

class SendReservationCreatedEmail
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
     * @param  ReservationCreated  $event
     * @return void
     */
    public function handle(ReservationCreated $event)
    {
        if ($event->isSendEmail) {
            // $user = $event->user;
            // $user->notify(new ReservationCreatedNotification($event->group));

            $emailData = [
                'toEmail' => $event->user->email,
                'emailType' => 'reservationCreated'
            ];

            SendEmail::dispatch($emailData, $event->group);
        }
    }
}
