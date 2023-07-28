<?php

namespace App\Notifications;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class HotelCreated extends Notification
{
    use Queueable;

    protected $hotel;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Hotel $hotel)
    {
        $this->hotel = $hotel;
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
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $defaultUser = $this->hotel
            ->users()
            ->where('is_default', true)
            ->first();

        $hotelType = $this->hotel->hotelType ? mb_strtolower($this->hotel->hotelType->name) : "зочид буудал";
        
        return (new MailMessage)
                    ->bcc(['sales@ihotel.mn'])
                    ->subject('Шинэ '. $hotelType .' бүртгүүллээ.')
                    ->greeting(trans('messages.greetings'))
                    ->line('MyHotel RMS системд шинэ '. $hotelType .' бүртгүүллээ.')
                    ->line('Байгууллагын нэр: ' . $this->hotel->name)
                    ->line('Холбоо барих: ' . $defaultUser->phone_number)
                    ->action('Идэвхжүүлэх', config('services.dashboard.activateUrl') . $this->hotel->id . '?from=mail');
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
