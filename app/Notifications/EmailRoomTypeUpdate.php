<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

// Import onesignal classes
// use App\Channels\OneSignalChannel;
// use OneSignal;

class EmailRoomTypeUpdate extends Base
{
    public $emailData;
    public $roomType;
    public $emailType;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($emailData, $roomType)
    {
        $this->emailData = $emailData;
        $this->roomType = $roomType;
        $this->emailType = $emailData['emailType'];

        // Build notification data
        $emailType = $this->emailType;
        $source = $emailData['source'];
        $event = 'update';
        $message = $roomType->hotel->name . ' ' . $roomType->name . '-ийн үндсэн үнэ өөрчлөгдлөө.';

        // Set message
        $this->message = $message;

        // Set notification data
        $this->data = [
            'message' => $this->message,
            'hotelId' => $roomType->hotel_id,
            'event' => $event,
            'type' => 'roomTypeUpdate',
            'dataId' => $roomType->id
        ];

        // Define notify channels
        $channels = ['database', 'broadcast'];

        if (config('mail.password') != '' && config('mail.username') != '') {
            array_push($channels, 'mail');
        }

        // if (config('onesignal.app_id') != '' && config('onesignal.rest_api_key') != '') {
        //     array_push($channels, OneSignalChannel::class);
        // }

        // Set channels
        $this->channels = $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $actionUrl = config('services.dashboard.baseUrl') . '/p/' . $this->roomType->hotel_id . '/s/room/types';

        return (new MailMessage)
                ->bcc(is_null($this->emailData['bccEmails']) ? [] : $this->emailData['bccEmails'])
                ->subject($this->message)
                ->markdown('mail.roomTypeUpdate', [
                    'roomType' => $this->roomType,
                    'emailType' => $this->emailType,
                    'user' => $this->emailData['user'],
                    'actionUrl' => $actionUrl,
                ]);
    }
}
