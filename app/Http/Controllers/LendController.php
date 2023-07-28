<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{ ReservationPaymentMethod };
use App\Jobs\SendEmail;
use App\Traits\ReservationTrait;
// use Guzzle\Http\Exception\ServerException;

class LendController extends Controller
{
    use ReservationTrait;

    public function api($url, $params)
    {
        $http = new \GuzzleHttp\Client;

        // try {
        $response = $http->post($url, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'x-and-auth-token' => config('services.lendmn.token'),
            ],
            'form_params' => $params,
        ]);
        //     $response = json_decode((string) $response->getBody(), true);
        // } catch (ServerException $e) {
        //     $response = json_decode((string) $e->getResponse()->getBody(true), true);
        // }

        return json_decode((string) $response->getBody(), true);
    }

    public function generateInvoice(Request $request)
    {
        $params = [
            'amount' => $request->input('amount'),
            'description' => 'Айхотел төлбөр',
            'duration' => '172800',
        ];

        return $this->api(config('services.lendmn.url') . '/w/invoices', $params);
    }

    public function checkPayment(Request $request)
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->get(config('services.lendmn.url') . '/w/invoices/' . $request->input('number'), [
            'headers' => [
                'x-and-auth-token' => config('services.lendmn.token'),
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function cancelInvoice(Request $request)
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->delete(config('services.lendmn.url') . '/w/invoices/' . $request->input('number'), [
            'headers' => [
                'x-and-auth-token' => config('services.lendmn.token'),
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function webHook(Request $request)
    {
        if ($request->input('eventType') === 'invoice.paid') {
            $http = new \GuzzleHttp\Client;
            $response = $http->post(url('lend/check/payment'), [
                'headers' => [
                    'access-key' => config('services.xroom.access_key'),
                ],
                'form_params' => [
                    'number' => $request->input('data.invoiceNumber'),
                ],
            ]);
            $lendResponse = json_decode((string) $response->getBody(), true);

            if ($lendResponse['code'] == 0 && $lendResponse['response']['status'] === 1) {
                $reservationPaymentMethod = ReservationPaymentMethod::where('lend_invoice_number', $lendResponse['response']['invoiceNumber'])
                    ->first();

                if (!is_null($order)) {
                    $reservationPaymentMethod->lend_transaction = serialize($lendResponse['response']);
                    $reservationPaymentMethod->save();

                    $paymentMethod = $reservationPaymentMethod->payment_method;

                    // Confirm reservation
                    $this->confirmReservation($reservationPaymentMethod, $paymentMethod, $lendResponse);
                } else {
                    // Create error log
                    Log::error('notifyLendPayment: ', [
                        'message' => 'Order not found. Invoice Number: ' . $lendResponse['response']['invoiceNumber'],
                        'requestUrl' => '',
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true
        ]);
    }
}
