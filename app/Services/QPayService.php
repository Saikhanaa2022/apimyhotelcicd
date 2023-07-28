<?php

namespace App\Services;

use App\Http\Requests\CheckBillRequest;
use GuzzleHttp\Client;
use InvalidArgumentException;

use function GuzzleHttp\json_decode;
use Carbon\Carbon;

class QPayService
{
    public function getClient()
    {
        $url = config('services.xroom.qpay.base_url');
        return new Client(['base_uri' => $url]);
    }

    public function getToken()
    {
        $basicAuth = config('services.xroom.qpay.basic_auth');

        info('basicAuth' . $basicAuth);

        $response = $this->getClient()->request('POST', '/v2/auth/token', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($basicAuth)
            ]
        ]);

        return json_decode((string) $response->getBody());
    }

    public function createInvoice($total_price, $order_code, $description, $user_id)
    {
        $now = Carbon::now();
        $merchant = config('services.xroom.qpay.invoice_code');
        $token = $this->getToken()->access_token;
        info('token' . $token);
        info('merchant' . $merchant);

        $body = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token
            ],
            'json' => [
                "invoice_code" => $merchant,
                "sender_invoice_no" => '' . $order_code,
                "invoice_description" => $description,
                "invoice_receiver_code" => $user_id,
                "amount" => (float) $total_price,
                "callback_url" => config('app.url') . "/api/xroom/qpay-payment-check?invoice_no=" . $order_code,
            ]
        ];

        info($body);

        $response = $this->getClient()->request('POST', '/v2/invoice', $body);

        return $response->getBody();
    }

    public function check(CheckBillRequest $request)
    {
        $token = $this->getToken()->access_token;
        info('url' . '/v1/payment/check/' . $request->payment_id);
        try {
            $response = $this->getClient()->request('GET', '/v1/payment/check/' . $request->payment_id, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ]);

            if ($response->getStatusCode() == 200) {
                return json_decode($response->getBody());
            }

            throwException(new \Exception("QPAY BILL CHECK ERROR: " . $response->getReasonPhrase(), $response->getStatusCode()));
        } catch (InvalidArgumentException $ex) {
            report($ex);
        }
    }
}