<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends Notification
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        $greeting = trans('messages.greetings');
        $subject = 'Нууц үг сэргээх хүсэлт хүлээн авлаа';
        $body = 'Энэхүү мэйл нь таны нууц үг сэргээх хүсэлтийн дагуу илгээгдэж байгаа болно. Та доор байрлах товч дээр дарснаар таны нууц үг сэргээх хуудсыг нээнэ.';
        $action = 'Нууц үг сэргээх';
        // if (\App::isLocale('en')) {
        //     $subject = 'Reset Password Notification';
        //     $body = 'You are receiving this email because we received a password reset request for your account.';
        //     $action = 'Reset Password';
        // }   
        return (new MailMessage)
                    ->subject($subject)
                    ->greeting($greeting)
                    ->line($body)
                    ->action($action, config('services.dashboard.passwordResetUrl') . '/' . $this->token);
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
