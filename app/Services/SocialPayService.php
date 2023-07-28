<?php

namespace App\Services;

use SimpleXMLElement;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Services\Common;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class SocialPayService
{

    public function getClient()
    {
        $url = config('services.xroom.socialpay.base_url');
        $client = new Client([
            'verify' => false,
            // 'curl' => [ 
            //   CURLOPT_SSL_VERIFYPEER => false, 
            //   CURLOPT_SSL_VERIFYHOST => 2 
            // ],
            'base_uri' => $url,
        ]);
        // $client->setDefaultOption('verify', false);
        return $client;
        // return new Client(['base_uri' => $url]);
    }

    public function createOrder($total_price, $invoice_no, $description)
    {
        $merchant = config('services.xroom.socialpay.key_number');

        $xmlr = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><TKKPG></TKKPG>");

        $request = new SimpleXMLElement("<Request></Request>");
        $request->addChild('Operation', 'CreateOrder');
        $request->addChild('Language', 'EN');
        info('description ', $description);
        $order = new SimpleXMLElement("<Order></Order>");
        $order->addChild('OrderType', 'Purchase');
        $order->addChild('Merchant', $merchant);
        $order->addChild('Amount', '' . ($order->total_price * 100));
        $order->addChild('Currency', 496);
        $order->addChild('Description', $description);
        Common::sxml_append($request, $order);
        Common::sxml_append($xmlr, $request);

        $xml = $xmlr->asXML();
        info($xml);
        $body = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8'
            ],
            'body' => $xml
        ];

        $response = $this->getClient()->request('POST', '/Exec', $body);
        $result = new SimpleXMLElement($response->getBody());
        info($result->Response);
        if ($result->Response->{'Status'} == '00') {
            return Common::xml2array($result->Response->Order);
        }

        throwException(new \Exception('Системийн алдаа', 500));
    }

    public function getOrderStatus($order_id, $session_id)
    {
        $merchant = config('services.xac.merchant');

        $xmlr = new SimpleXMLElement("<?xml version='1.0' encoding='UTF-8'?><TKKPG></TKKPG>");

        $request = new SimpleXMLElement("<Request></Request>");
        $request->addChild('Operation', 'GetOrderStatus');
        $request->addChild('Language', 'EN');

        $order = new SimpleXMLElement("<Order></Order>");
        $order->addChild('OrderID', $order_id);
        $order->addChild('Merchant', $merchant);

        Common::sxml_append($request, $order);
        $request->addChild('SessionID', $session_id);

        Common::sxml_append($xmlr, $request);

        $xml = $xmlr->asXML();
        info($xml);
        $body = [
            'headers' => [
                'Content-Type' => 'text/xml; charset=UTF8'
            ],
            'body' => $xml
        ];

        $response = $this->getClient()->request('POST', '/Exec', $body);
        $result = new SimpleXMLElement($response->getBody());
        info($result->Response);
        if ($result->Response->{'Status'} == '00') {
            return Common::xml2array($result->Response->Order);
        }

        throwException(new \Exception('Системийн алдаа', 500));
    }
}