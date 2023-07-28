<?php

namespace App\Listeners;

use App\Events\XRoomReservationCreated;
use App\Services\ReservationService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class CreateXRoomReservation
{
    private $reservationService;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReservationService $reservationService)
    {
        //
        $this->reservationService = $reservationService;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        //
        $result = $this->reservationService->createReservation($event->reservation);
        // Log::error('test' + serialize($result));
        if ($result['success'] == true) {
            // event(new XRoomReservationCreated($event->reservation));
        } else {
            // dd($result);
            // Log::error('error' + (string) $result);
        }
    }
}