<?php

namespace App\Notifications;

use App\Models\Hotel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class HotelActivated extends Notification
{
    use Queueable;
    protected $hotel;
    protected $state;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Hotel $hotel, $state)
    {
        $this->hotel = $hotel;
        $this->state = $state;
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
            'mail'
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

        $str = 'Таны ' . $this->hotel->name . ' удирдах эрх';

        if ($this->state) {
            return (new MailMessage)
                    ->subject($str . ' идэвхжилээ.')
                    ->greeting(trans('messages.greetings'))
                    ->line($str . ' амжилттай баталгаажлаа.')
                    ->line('Нэвтрэх имэйл хаяг: ' . $defaultUser->email)
                    ->action('Системд нэвтрэх', config('services.dashboard.loginUrl'))
                    ->line('MyHotel RMS системийг сонгосонд баярлалаа!');
        }

        return (new MailMessage)
                ->subject($str . ' цуцлагдлаа.')
                ->greeting(trans('messages.greetings'))
                ->line($str . ' цуцлагдлаа.')
                ->line('MyHotel RMS системийг сонгосонд баярлалаа!');
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
