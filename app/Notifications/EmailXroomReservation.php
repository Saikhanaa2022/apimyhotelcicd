<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

// Import onesignal classes
use App\Channels\OneSignalChannel;
use OneSignal;

class EmailXroomReservation extends Base
{
    public $emailData;
    public $reservation;
    public $emailType;
    public $message;
    public $year;

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
        $this->year = Carbon::now()->year;

        // Build notification data
        $emailType = $this->emailType;
        $source = $emailData['source'];
        $event = 'create';
        $message = $reservation->hotel->name . ' ' .  substr($reservation->guestClone->phone_number , -4) . '- дугаартай XRoom -ийн захиалга баталгаажлаа.';

        // Set message
        $this->message = $message;
       
        // Set notification data
        $this->data = [
            'message' => $this->message,
            'hotelId' => $reservation->hotel_id,
            'event' => $event,
            'reservation_id' => $reservation->id,
            'type' => 'xroomCreateReservation',
            'dataId' => $reservation->group_id
        ];

        // Define notify channels
        $channels = ['database', 'broadcast'];

        if (config('mail.password') != '' && config('mail.username') != '') {
            array_push($channels, 'mail');
        }

        if (config('onesignal.app_id') != '' && config('onesignal.rest_api_key') != '') {
            array_push($channels, OneSignalChannel::class);
        }

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
        $actionUrl = config('services.dashboard.baseUrl') . '/p/' . $this->reservation->hotel_id . '/reservation/' . $this->reservation->id;

        return (new MailMessage)
                ->bcc(is_null($this->emailData['bccEmails']) ? [] : $this->emailData['bccEmails'])
                ->subject($this->message)
                ->markdown('mail.xroomConfirmReservation', [
                    'reservation' => $this->reservation,
                    'emailType' => $this->emailType,
                    'amount' => $this->emailData['amount'],
                    'payment_method' => $this->emailData['payment_method'],
                    'year' => $this->year,
                    'actionUrl' => $actionUrl,
                ]);
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
