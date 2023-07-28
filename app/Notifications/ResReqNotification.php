<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

// Import onesignal classes
use App\Channels\OneSignalChannel;
use OneSignal;

class ResReqNotification extends Base
{
    public $emailData;
    public $resReq;
    public $emailType;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($emailData, $resReq)
    {
        $this->emailData = $emailData;
        $this->resReq = $resReq;
        $this->emailType = $emailData['emailType'];

        // Build notification data
        $emailType = $this->emailType;
        $source = $emailData['source'];
        $event = 'created';
        $message = $resReq->res_number . ' дугаартай захиалгын хүсэлт ';

        if ($emailType === 'resReqCreated') {
            $message = $source . '-c ' . $message . 'ирлээ.';
        } else if ($emailType === 'resReqConfirmed') {
            $event = 'confirmed';
            $message = $message . 'баталгаажлаа.';
        } else if ($emailType === 'resReqPaid') {
            $event = 'paymentPaid';
            $message = $message . '-н төлбөр төлөгдлөө.';
        }

        // Set message
        $this->message = $message;

        // Set notification data
        $this->data = [
            'message' => $this->message,
            'hotelId' => $resReq->hotel_id,
            'event' => $event,
            'type' => 'resRequest',
            'dataId' => $resReq->id,
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
        $actionUrl = config('services.dashboard.baseUrl') . '/p/' . $this->resReq->hotel_id . '/res-request/' . $this->resReq->id;

        return (new MailMessage)
                ->bcc(is_null($this->emailData['bccEmails']) ? [] : $this->emailData['bccEmails'])
                ->subject($this->message)
                ->markdown('mail.resReq', [
                    'resReq' => $this->resReq,
                    'emailType' => $this->emailType,
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
