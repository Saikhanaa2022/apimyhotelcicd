<?php

namespace App\Events;

use App\Models\XRoomReservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class XRoomReservationCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public $afterCommit = true;

    public $reservation;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(XRoomReservation $reservation)
    {
        $this->reservation = $reservation;
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