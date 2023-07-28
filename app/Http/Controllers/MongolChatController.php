<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use App\Traits\ReservationTrait;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;

class MongolChatController extends Controller
{
    use ReservationTrait;

    public function apiWorker($url, $params)
    {
        $http = new Client;
        $requestUrl = config('services.mongolchat.base_url') . config('services.mongolchat.prefix') . $url;

        $response = $http->post($requestUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => config('services.mongolchat.worker_auth'),
                'api-key' => config('services.mongolchat.api_key'),
            ],
            'body' => json_encode($params),
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function generateQr(Request $request)
    {
        $params = [
            'amount' => $request->input('amount'),
            'products' => $request->input('products'),
            'title' => 'Xroom',
            'sub_title' => '',
            'noat' => '',
            'nhat' => '',
            'ttd' => '',
            'reference_number' => $request->input('reference_number'),
            'expire_time' => '720'
        ];

        return $this->apiWorker('/worker/onlineqr/generate', $params);
    }

    public function qrStatus(Request $request)
    {
        $params = [
            'qr' => $request->input('qr')
        ];

        return $this->apiWorker('/worker/onlineqr/status', $params);
    }

    public function settle(Request $request)
    {
        $params = [
            'transactions' => $request->input('transactions')
        ];

        return $this->apiWorker('/worker/settle/upload', $params);
    }

    public function refund(Request $request)
    {
        $params = [
            'id' => $request->input('trans_id')
        ];

        return $this->apiWorker('/worker/transaction/refund', $params);
    }

    public function mcWebhook(Request $request)
    {
        $requestUrl = url('mc/check/payment');
        $params = $request->all();

        $order = \App\Order::where('number', $params['data']['reference_number'])
            ->first();

        if (!is_null($order)) {
            $order->mc_trans_id = $params['data']['transaction_id'];
            $order->update();
            // Confirm reservation
            $this->confirmOrder($order, 'mongolChat');
        } else {
            // Create error log
            Log::error('MongolChatPayment: ', [
                'log_type' => 'internal',
                'action' => 'mcWebhook',
                'request_url' => $requestUrl,
                'status_code' => null,
                'related_model' => null,
                'model_id' => null,
                'exception' => 'Order not found. Order number:' . $params['data']['reference_number']
            ]);
        }

        return response()->json([
            'success' => true,
        ], 200);
    }
}
