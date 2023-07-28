<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class UserInvited extends Notification
{
    use Queueable;
    protected $user;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $action = 'Системд нэвтрэх';
        
        return (new MailMessage)
            ->subject('Таныг MyHotel RMS системд хэрэглэгчээр нэмлээ')
            ->greeting(trans('messages.greetings'))
            ->line('Таныг MyHotel RMS системд хэрэглэгчээр нэмлээ.')
            // ->line('Байгууллагын нэр: ' . $this->uhotel->name)
            ->line('Нэвтрэх имэйл хаяг: ' . $this->user->email)
            // ->line('Нэвтрэх нууц үг: ' . $this->user->password)
            ->action($action, config('services.dashboard.loginUrl'));
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
