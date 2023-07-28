<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use App\Models\Contact;

class NotifyContactCreated extends Notification
{
    use Queueable;

    public $contact;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact; 
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
        $contact = $this->contact;

        return (new MailMessage)
                    ->subject('Холбоо барих хүсэлт ирлээ.')
                    ->line('Буудлын нэр: ' . $contact->hotel_name)
                    ->line('Нэр: ' . $contact->contact_name)
                    ->line('Албан тушаал: ' . $contact->position)
                    ->line('Утасны дугаар: ' . $contact->phone_number)
                    ->line('Цахим хаяг: ' . $contact->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
