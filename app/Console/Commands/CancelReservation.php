<?php

namespace App\Console\Commands;

use App\ErrorLog;
use Illuminate\Console\Command;
use App\Models\{Hotel, Reservation, Cancellation, Guest, GuestClone, SourceClone, TaxClone, UserClone, ReservationPaymentMethod};
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use App\Traits\ReservationTrait;

class CancelReservation extends Command
{
    use ReservationTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'everyminute:cancelReservation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Every minute cancel reservations for xroom';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $filtered = Reservation::where('status','=','pending')->whereHas('sourceClone', function ($query) {
            $query->where('service_name', 'xroom');
        })
        ->with(['sourceClone'])
        ->with(['cancellationPolicyClone'])
        ->get();

        $reservations = [];

        foreach ($filtered as $reservation) {
            $current = Carbon::parse($reservation->created_at)->addMinutes(20)->format('Y-m-d h:m:s');
            $now = Carbon::now()->format('Y-m-d h:m:s');
            if ($current < $now) {
                $reservations[] = $reservation;
            }
        }

        try {
            $totalPenalty = 0;

            foreach($reservations as $reservation) {

                // dd($reservation);
                $reservationPaymentMethod = ReservationPaymentMethod::where('group_id', $reservation->group_id)->first();

                // cancelReservations
                if ($reservationPaymentMethod)
                $this->cancelOrder($reservation->group_id, $reservationPaymentMethod);

                // cancelInvoices
                if ($reservationPaymentMethod) {
                    if ($reservationPaymentMethod->lend_qr_string && $reservationPaymentMethod->paid === 0) {
                        $http = new \GuzzleHttp\Client;
                        try {
                            $response = $http->post(url('lend/cancel/invoice'), [
                                'headers' => [
                                    'access-key' => config('services.xroom.access_key'),
                                ],
                                'verify' => false,
                                'form_params' => [
                                    'number' => $reservationPaymentMethod->lend_invoice_number,
                                ],
                            ]);
                        } catch(\RequestException $e) {
                            ErrorLog::create([
                                'log_type' => 'internal',
                                'action' => 'cancelReservation Xroom',
                                'request_url' => NULL,
                                'status_code' => NULL,
                                'related_model' => NULL,
                                'model_id' => NULL,
                                'exception' => 'Something went wrong: ' . $e->getMessage()
                            ]);
                        }
                    } else if ($reservationPaymentMethod->qpay_qrcode &&    $reservationPaymentMethod->paid === 0 ) {
                        try{
                            $this->cancelQpayInvoice($reservationPaymentMethod->qpay_invoice_id);
                        } catch(\Exception $e) {
                            ErrorLog::create([
                                'log_type' => 'internal',
                                'action' => 'cancelReservation Xroom',
                                'request_url' => NULL,
                                'status_code' => NULL,
                                'related_model' => NULL,
                                'model_id' => NULL,
                                'exception' => 'Something went wrong: ' . $e->getMessage()
                            ]);
                        }
                    }
                }
            }
        } catch(Exception $e) {
            ErrorLog::create([
                'log_type' => 'internal',
                'action' => 'cancelReservation Xroom',
                'request_url' => NULL,
                'status_code' => NULL,
                'related_model' => NULL,
                'model_id' => NULL,
                'exception' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }

        dd('Every Minute cancel reservations successfully');
    }

    public function cancelQpayInvoice($id)
    {
        $http = new \GuzzleHttp\Client;

        $response = $http->post(config('services.qpay.base_url') . '/auth/token', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(config('services.qpay.account_v2')),
            ]
        ]);

        $tokenResponse = json_decode((string) $response->getBody(), true);
        
        $response = $http->delete(config('services.qpay.base_url') . '/invoice/' . $id, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $tokenResponse['access_token'],
            ]
        ]);
    }
}
