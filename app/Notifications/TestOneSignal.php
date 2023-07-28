<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

// Import onesignal classes
use App\Channels\OneSignalChannel;
use OneSignal;

class TestOneSignal extends Notification
{
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->message = "Test construct message.";
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            OneSignalChannel::class
        ];
    }

    /**
     * Get the one signal message representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return OneSignal
     */
    public function toOneSignal($notifiable)
    {
        // Get user external id
        $userId = '' . $notifiable->id . '';

        return OneSignal::sendNotificationToExternalUser(
                $this->message,
                $userId,
                $url = null,
                $data = [
                    "message" => "Test message.",
                    "hotelId" => 1,
                    "event" => "created",
                    "type" => "resRequest",
                    "dataId" => 699
                ],
                $buttons = null,
                $schedule = null
            );
    }
}
