<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

// use App\Models\User;
// use App\Notifications\NotifyContactCreated;
use App\Jobs\SendEmail;

class SendEmailContactCreated
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
        // $user->email = env('NOTIFY_EMAIL', 'dev@ihotel.mn');

        // $user->notify(new NotifyContactCreated($event->contact));

        $emailData = [
            'toEmail' => env('NOTIFY_EMAIL', 'dev@ihotel.mn'),
            'emailType' => 'contactCreated'
        ];

        SendEmail::dispatch($emailData, $event->contact);
    }
}
