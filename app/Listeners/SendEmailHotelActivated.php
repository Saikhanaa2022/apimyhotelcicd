<?php

namespace App\Listeners;

use App\Events\HotelActivated;
// use App\Notifications\HotelActivated as HotelActivatedNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\SendEmail;

class SendEmailHotelActivated
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
    public function handle(HotelActivated $event)
    {
        $defaultUser = $event->hotel
            ->users()
            ->where('is_default', true)
            ->first();

        // $defaultUser->notify(new HotelActivatedNotification($event->hotel, $event->state));

        $emailData = [
            'toEmail' => $defaultUser->email,
            'emailType' => 'hotelActivated',
            'state' => $event->state
        ];

        SendEmail::dispatch($emailData, $event->hotel);
    }
}
