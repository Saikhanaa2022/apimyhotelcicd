<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;
use App\Models\{ReservationPaymentMethod, StoreToken};
use App\Jobs\SendEmail;
use App\Traits\ReservationTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class QpayController extends Controller
{
    use ReservationTrait;

    public function api($url, $params)
    {
        try {
            $http = new \GuzzleHttp\Client;

            $response = $http->post(config('services.qpay.base_url') . '/auth/token', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(config('services.qpay.account_v2')),
                ]
            ]);

            $tokenResponse = json_decode((string) $response->getBody(), true);

            $response = $http->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenResponse['access_token'],
                ],
                'json' => $params,
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = json_decode($response->getBody()->getContents());
            return response()->json($responseBodyAsString, $e->getCode());
        }
    }

    public function tokenAccessRefresh()
    {
        try {
            $http = new \GuzzleHttp\Client;

            $storeToken = StoreToken::first();
            $tokenResponse = null;
            if (is_null($storeToken)) {
                $response = $http->post(config('services.qpay.base_url') . '/auth/token', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode(config('services.qpay.account_v2')),
                    ]
                ]);
                $tokenResponse = json_decode((string) $response->getBody(), true);
                $storeToken = $this->storeToken($tokenResponse);
            } else {
                $checkDate = Carbon::now()->format('Y-m-d h:m:s');
                if ($storeToken->expires_in < $checkDate && $storeToken->refresh_expires_in < $checkDate) {
                    $response = $http->post(config('services.qpay.base_url') . '/auth/token', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Basic ' . base64_encode(config('services.qpay.account_v2')),
                        ]
                    ]);
                    $tokenResponse = json_decode((string) $response->getBody(), true);
                    $this->updateToken($storeToken, $tokenResponse);
                } else if ($storeToken->expires_in < $checkDate && $storeToken->refresh_expires_in > $checkDate) {
                    $response = $http->post(config('services.qpay.base_url') . '/auth/refresh', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $storeToken->refresh_token,
                        ]
                    ]);
                    $tokenResponse = json_decode((string) $response->getBody(), true);
                    $this->updateToken($storeToken, $tokenResponse);
                }
            }

            return [
                'success' => true,
                'token' => $storeToken->access_token
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getCode()
            ];
        }
    }

    public function generateInvoice(Request $request)
    {
        // Check Token
        $params = [
            "invoice_code" => "TEST_INVOICE", // iHOTEL_INVOICE
            "sender_invoice_no" =>  $request->input('number'),
            "invoice_receiver_code" => "terminal",
            "invoice_description" => $request->input('description'),
            "amount" =>  $request->input('amount'),
            "callback_url" => "https://api.myhotel.mn/qpay/payment?invoce_id=" . $request->input('number')
        ];

        return $this->api(config('services.qpay.base_url') . '/invoice', $params);
    }

    public function checkPayment(Request $request)
    {
        // Check Token

        $params = [
            "object_type" => "INVOICE",
            "object_id" => $request->input('number'),
            "offset" => [
                "page_number" => 1,
                "page_limit" =>  100
            ]
        ];

        return $this->api(config('services.qpay.base_url') . '/payment/check', $params);
    }

    public function qpayWebhook(Request $request)
    {
        $requestUrl = url('qpay/check/payment');

        $reservationPaymentMethod = ReservationPaymentMethod::where('number', $request->query('invoice_id'))
            ->first();

        if (!is_null($reservationPaymentMethod)) {
            try {
                $http = new Client;

                $response = $http->post($requestUrl, [
                    'headers' => [
                        'access-key' => config('services.xroom.access_key'),
                    ],
                    'verify' => false,
                    'form_params' => [
                        'client_id' => config('services.qpay.client_id'),
                        'client_secret' => config('services.qpay.client_secret'),
                        'number' => $reservationPaymentMethod->number,
                    ],
                ]);

                $qpayResponse = json_decode((string) $response->getBody(), true);

                if ($qpayResponse['count'] != 0) {
                    $reservationPaymentMethod->qpay_transaction = serialize($qpayResponse['rows']);
                    $reservationPaymentMethod->update();

                    // Confirm reservation
                    $this->confirmReservation($reservationPaymentMethod, 'qpay', $qpayResponse);
                } else {
                    // Create error log
                    Log::error('QpayPayment: ', [
                        'log_type' => 'request',
                        'action' => 'qpayWebhook',
                        'request_url' => $requestUrl,
                        'status_code' => $response->getStatusCode(),
                        'related_model' => 'Order',
                        'model_id' => $reservationPaymentMethod->res_id,
                        'exception' => 'Result code' . $qpayResponse['result_code'],
                    ]);
                }
            } catch (RequestException $e) {
                // Create error log
                Log::error('QpayPayment: ', [
                    'log_type' => 'request',
                    'action' => 'qpayWebhook',
                    'request_url' => $requestUrl,
                    'status_code' => $e->getCode(),
                    'related_model' => 'Order',
                    'model_id' => $reservationPaymentMethod->res_id,
                    'exception' => $e->getMessage(),
                    'requestUrl' => 'qpayWebhook',
                ]);
            }
        } else {
            // Create error log
            Log::error('QpayPayment: ', [
                'log_type' => 'internal',
                'action' => 'qpayWebhook',
                'request_url' => $requestUrl,
                'status_code' => null,
                'related_model' => null,
                'model_id' => null,
                'exception' => 'Order not found. invoiceId:' . $request->query('invoiceId')
            ]);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }

    public function updateToken($storeToken, $tokenResponse)
    {
        $storeToken->access_token = $tokenResponse['access_token'];
        $storeToken->refresh_token = $tokenResponse['refresh_token'];
        $storeToken->expires_in = Carbon::createFromTimestamp($tokenResponse['expires_in'])->format('Y-m-d h:m:s');
        $storeToken->refresh_expires_in = Carbon::createFromTimestamp($tokenResponse['refresh_expires_in'])->format('Y-m-d h:m:s');
        $storeToken->save();
    }

    public function storeToken($tokenResponse)
    {
        return StoreToken::create([
            'access_token' => $tokenResponse['access_token'],
            'refresh_token' => $tokenResponse['refresh_token'],
            'expires_in' => Carbon::createFromTimestamp($tokenResponse['expires_in'])->format('Y-m-d h:m:s'),
            'refresh_expires_in' => Carbon::createFromTimestamp($tokenResponse['refresh_expires_in'])->format('Y-m-d h:m:s')
        ]);
    }
}
