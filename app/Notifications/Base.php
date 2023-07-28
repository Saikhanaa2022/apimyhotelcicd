<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\{BroadcastMessage};
use Illuminate\Support\Carbon;

class Base extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    protected $channels = [
        'database', 'broadcast',
    ];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $this->channels;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->data;
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage(camelCaseKeys([
            'data' => $this->toArray($notifiable),
            'created_at' => Carbon::now()->toDateTimeString(),
            'is_fetched' => false,
            'read_at' => null,
        ]));
    }
}
