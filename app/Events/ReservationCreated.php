<?php

namespace App\Events;

use App\Models\{Group, User};
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReservationCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $group;
    public $user;
    public $isSendEmail;
    public $from;
    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Group $group, User $user, $isSendEmail = true, $from, $data = NULL)
    {
        $this->group = $group;
        $this->user = $user;
        $this->isSendEmail = $isSendEmail;
        $this->from = $from;
        $this->data = $data;
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
