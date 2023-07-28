<?php

namespace App\Listeners;

use App\Events\UserInvited;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\SendEmail;

class SendEmailUserInvited
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
    public function handle(UserInvited $event)
    {
        SendEmail::dispatch([
            'toEmail' => $event->user->email,
            'emailType' => 'userInvited'
        ]);
    }
}
