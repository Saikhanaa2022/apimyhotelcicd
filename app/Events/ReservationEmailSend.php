<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// use \App\Models\Group;

class ReservationEmailSend
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dataId;
    public $email;
    public $emailType;
    public $isGroup;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($dataId, $email, $emailType, $isGroup = false)
    {
        $this->dataId = $dataId;
        $this->email = $email;
        $this->emailType = $emailType;
        $this->isGroup = $isGroup;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
