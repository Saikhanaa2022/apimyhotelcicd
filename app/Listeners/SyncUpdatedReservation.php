<?php

namespace App\Listeners;

use App\Events\ReservationUpdated;
use App\Models\Reservation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;

class SyncUpdatedReservation
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $data = [];
        // Check hotel has ihotel service on
        foreach ($event->reservations as $item) {
            // Get room type sync id
            $roomTypeClone = $item->roomTypeClone;
            $roomSyncId = $roomTypeClone->sync_id;

            if (!is_null($roomSyncId)) {
                if (!is_null($item->sync_id)) {
                    $data[] = [
                        'id' => $item->sync_id,
                        'startDate' => $item->check_in,
                        'endDate' => $item->check_out,
                        'status' => $item->status
                    ];
                }
            }
        }

        if (count($data) > 0) {
            try {
                // Send update reservation request to ihotel
                $http = new Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/update/block', [
                    'json' => [
                        'data' => $data,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);
            } catch (RequestException $e) {
            }
        }
    }
}
