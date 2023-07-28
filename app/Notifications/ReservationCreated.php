<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Group;

class ReservationCreated extends Notification
{
    use Queueable;
    protected $group;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Group $group)
    {
        $this->group = $group;
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
        $actionUrl = config('services.dashboard.baseUrl') . '/p/' . $this->group->hotel_id . '/group/' . $this->group->id;
        $firstRes = $this->group->reservations[0];
        $hotel = $firstRes->hotel;

        return (new MailMessage)
            ->bcc(is_null($hotel->hotelSetting->bcc_emails) ? [] : $hotel->hotelSetting->bcc_emails)
            ->subject('MyHotel - Шинэ захиалга бүртгэгдлээ.')
            ->markdown('mail.hotel.reservationHotel', [
                'group' => $this->group,
                'firstRes' => $firstRes,
                'actionUrl' => $actionUrl,
            ]);
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
