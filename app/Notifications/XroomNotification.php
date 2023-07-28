<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

// Import onesignal classes
use App\Channels\OneSignalChannel;
use OneSignal;

class XroomNotification extends Base
{
    public $emailData;
    public $reservation;
    public $emailType;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($emailData, $reservation)
    {
        $this->emailData = $emailData;
        $this->reservation = $reservation;
        $this->emailType = $emailData['emailType'];

        // Build notification data
        $emailType = $this->emailType;
        $source = $emailData['source'];
        $event = 'created';
        $message = 'Xroom - Шинэ захиалга бүртгэгдлээ.';
        // Set message
        $this->message = $message;
        // Set notification data
        $this->data = [
            'message' => $this->message,
            'hotelId' => $reservation->hotel->id,
            'event' => $event,
            'type' => 'newReservationXroom',
            'dataId' => $reservation->group_id,
            'reservationId' => $reservation->id,
        ];

        // Define notify channels
        $channels = ['database', 'broadcast'];

        if (config('onesignal.app_id') != '' && config('onesignal.rest_api_key') != '') {
            array_push($channels, OneSignalChannel::class);
        }

        // Set channels
        $this->channels = $channels;
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
        $userId = ''. $notifiable->id . '';

        return OneSignal::sendNotificationToExternalUser(
                $this->message,
                $userId,
                $url = null,
                $data = $this->data,
                $buttons = null,
                $schedule = null
            );
    }
}
