<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\RoomTypeUpdated;
use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendEmail;

class SyncUpdatedRoomType
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
        $roomType = $event->roomType;
        $requestUrl = config('services.ihotel.baseUrl') . '/update/roomType';
        // Declare data array
        $data = [
            'id' => $roomType->sync_id,
            'roomTypeId' => $roomType->id,
            'defaultPrice' => $roomType->default_price
        ];

        try {
            // Check sync hotel & room
            if ($roomType->hotel->has_ihotel && !is_null($roomType->sync_id)) {
                // Send request to ihotel
                $http = new Client;
                $response = $http->post($requestUrl, [
                    'json' => $data,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $res = json_decode((string) $response->getBody(), true);

                // Check status
                if ($res['status'] == false) {
                    Log::error('Room type sync error: ', [
                        'message' => $res['message'],
                        'requestUrl' => $requestUrl,
                        'payload' => $data
                    ]);
                } else {
                    $user = $roomType->hotel->users->pluck('email')->toArray();
                    array_push($user, 'info@ihotel.mn');
                    $emailData = [
                        'toEmail' => $roomType->hotel->res_email,
                        'bccEmails' => $user,
                        'emailType' => 'roomTypeUpdate',
                        'source' => 'rms.myhotel.mn',
                        'user' => auth()->user()->name,
                    ];

                    // Trigger event
                    SendEmail::dispatch($emailData, $roomType);
                }
            }
        } catch (RequestException $e) {
            Log::error('Room type sync error: ', [
                'message' => $e->getMessage(),
                'requestUrl' => $requestUrl,
                'payload' => $data
            ]);
        }
    }
}
