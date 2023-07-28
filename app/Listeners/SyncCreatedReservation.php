<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use App\Models\Reservation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;

class SyncCreatedReservation
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
        if ($event->from !== 'ihotel') {
            $group = $event->group;
            $blocks = [];
           
            // Check hotel is connected to ihotel
            foreach ($group->reservations as $item) {
                $roomTypeClone = $item->roomTypeClone;
                $roomSyncId = $roomTypeClone->sync_id;
                if (!is_null($roomSyncId)) {
                    $blocks[] = [
                        // 'id' => !is_null($item->sync_id) ? $item->sync_id : null,
                        'syncId' => $item->id,
                        'roomSyncId' => $roomSyncId,
                        'startDate' => $item->check_in,
                        'endDate' => $item->check_out,
                        'number' => 1,
                    ];
                }
            }
            

            if (count($blocks) > 0) {
                $resReqData = NULL;
                
                if ($event->from === 'resReq') {
                    $data = $event->data;
                    $resReqData = [
                        'syncId' => $data['syncId'],
                        'checkIn' => $data['checkIn'],
                        'checkOut' => $data['checkOut'],
                    ];
                }

                try {
                    // Send create block request to ihotel
                    $http = new Client;
                    $response = $http->post(config('services.ihotel.baseUrl') . '/create/blocks', [
                        'json' => [
                            'isReserved' => true,
                            'blocks' => $blocks,
                            'resReqData' => $resReqData,
                        ],
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ]
                    ]);
        
                    $res = json_decode((string) $response->getBody(), true);
                    
                    // Get status
                    if ($res['status'] == true) {
                        $blocks = $res['blocks'];
                        foreach ($blocks as $item) {
                            $reservation = Reservation::find($item['id']);
                            if (!is_null($reservation)) {
                                $reservation->sync_id = $item['syncId'];
                                $reservation->save();
                            }
                        }
                    }
                } catch (\Exception $e) {
                   
                }
            }
        }
    }
}
