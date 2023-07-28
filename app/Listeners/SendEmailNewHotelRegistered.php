<?php

namespace App\Listeners;

// use App\Models\User;
use App\Events\HotelCreated;
// use App\Notifications\HotelCreated as HotelCreatedNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\SendEmail;

class SendEmailNewHotelRegistered
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
    public function handle(HotelCreated $event)
    {
        // $user = new User;
        // $user->email = env('NOTIFY_EMAIL', 'dev@ihotel.mn');

        $emailData = [
            'toEmail' => env('NOTIFY_EMAIL', 'dev@ihotel.mn'),
            'emailType' => 'hotelCreated'
        ];

        SendEmail::dispatch($emailData, $event->hotel);

        // $user->notify(new HotelCreatedNotification($event->hotel));
    }
}
