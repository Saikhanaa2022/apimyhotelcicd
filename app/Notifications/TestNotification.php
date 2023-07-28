<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class TestNotification extends Base
{
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->channels = ['mail'];
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data = $this->data;
        $msg = 'MyHotel.mn системээс туршилтын и-мэйл ирлээ.';

        return (new MailMessage)
                    ->bcc(is_null($data['bccEmails']) ? [] : $data['bccEmails'])
                    ->subject($msg)
                    ->greeting('Сайн байна уу?')
                    ->line($msg);
    }
}
