<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\{
    Reservation, Group
};
use Illuminate\Support\Collection;

use App\ErrorLog;
use App\Traits\ReservationTrait;

use \GuzzleHttp\Client;
use \GuzzleHttp\Exception\RequestException;

class VatController extends Controller
{
    public function productList(Request $request)
    {
        $http = new Client;
        $requestUrl = config('services.artLab.url') . '/product/list';
        $params = [];
        $params = $request->except('token');

        try {
            $response = $http->request('GET', $requestUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $request->get('token'),
                ],
                'query' => $params,
            ]);
        } catch (\Exception $th) {
            return $th->getMessage();
        }

        return json_decode((string) $response->getBody(), true);
    }

    private function toVatps($params, $id, $token)
    {
        $http = new Client;
        $requestUrl = config('services.artLab.url') . '/pos/to-vatps/' . $id;

        // $params = [
        //     'type' => $request->input('type'),
        //     'regNum ' => $request->input('regNum', ''),
        // ];

        try {
            $response = $http->post($requestUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => json_encode($params),
            ]);
        } catch (\Exception $th) {
            return $th->getMessage();
        }

        return json_decode((string) $response->getBody(), true);
    }

    private function posBill($params, $token)
    {
        $http = new Client;
        $requestUrl = config('services.artLab.url') . '/pos/bill';
        try {
            $response = $http->post($requestUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ],
                'body' => json_encode($params)
            ]);
        } catch (\Exception $th) {
            return $th->getMessage();
        }

        return json_decode((string) $response->getBody(), true);
    }

    private function check($token = null)
    {
        if (!is_null($token)) {
            $http = new Client;
            $requestUrl = config('services.artLab.url') . '/auth/check';

            try {
                $response = $http->request('GET', $requestUrl, [
                    'headers' => [
                        'Authorization' => $token,
                    ]
                ]);
            } catch (\Exception $th) {
                return $th->getMessage();
            }

            return json_decode((string) $response->getBody(), true);
        } else {
            return 1;
        }
    }

    private function login()
    {
        $http = new Client;
        $requestUrl = config('services.artLab.url') . '/auth/login';

        $params = [
            'username' => config('services.artLab.username'),
            'password' => config('services.artLab.password'),
            'type' => 'api',
        ];
        try {
            $response = $http->post($requestUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($params),
            ]);
        } catch (\Exception $th) {
            return $th->getMessage();
        }
        return implode($response->getHeader('Authorization'));
    }

    private function vatData($id = null)
    {
        $group = Group::find($id);
        if (!is_null($group)) {
            $reservations = $group->reservations;
            $value = 0;
            $totalDiscount = 0;
            $totalAmount = 0;
            $payments = collect();
            $products = collect();
            $taxes = collect();
            foreach ($reservations as $reservation) {
                $roomTypeName = $reservation->roomTypeClone->name;
                $value = 0;
                foreach ($reservation->dayRates as $dayRate) {
                    $value += $dayRate->value;
                }
                if ($reservation->discount_type === 'currency') {
                    $discount = $reservation->discount;
                } else {
                    $discount = ($value * $reservation->discount) / (100 - $reservation->discount);
                }
                // $tax = ($reservation->taxPercentage() * $value) / 100;
                // $amount = $tax + $value + $discount;
                $amount = $value + $discount;
                $product = [
                    'name' => $reservation->roomTypeClone->name,
                    'qty' => 1,
                    'price' => $amount
                ];
                $products->push($product);
                $totalDiscount += $discount;
                $totalAmount += $amount;
                foreach ($reservation->items as $item) {
                    // $price = $item->price + ($reservation->taxPercentage() * $item->price) / 100;
                    $product = [
                        'name' => $item->serviceClone->name,
                        'qty' => $item->quantity,
                        'price' => $item->price
                    ];
                    $products->push($product);
                    $totalAmount += $item->price * $item->quantity;
                }
                $cash = 0;
                $card = 0;
                foreach ($reservation->payments as $payment) {
                    $pay = json_decode($payment->income_pays);
                    if ($pay[0]->payment_method === 'Бэлэн мөнгө') {
                        $cash += $payment->amount;
                    } else {
                        $card += $payment->amount;
                    }
                }
                $payments = array_add($payments, 'cash', $cash);
                $payments = array_add($payments, 'card', $card);
                foreach ($reservation->taxClones as $tax) {
                    if ($tax->is_enabled) {
                        $taxes->push([
                            'name' => $tax->name,
                            'value' => round($reservation->amount - ($reservation->amount / (1 + ($tax->percentage / 100))), 2),
                            'percent' => $tax->percentage
                        ]);
                    }
                }
            }
            $groups = $taxes->groupBy('name');

            // we will use map to cumulate each group of rows into single row.
            // $group is a collection of rows that has the same opposition_id.
            $groupwithcalc = $groups->map(function ($group) {
                return [
                    'name' => $group->first()['name'], // opposition_id is constant inside the same group, so just take the first or whatever.
                    'value' => round($group->sum('value'), 2),
                ];
            });
            $amount = $totalAmount - $totalDiscount;
            $taxAmount = $amount - round($groupwithcalc->sum('value'), 2);
            // $taxAmount = round($amount / 1.1, 2);
            // $tax = round($amount - $taxAmount, 2);
            $vatData = [
                'id' => $group->id,
                'date' => $group->updated_at,
                'ddtd' => $group->ddtd,
                'qr' => $group->qr,
                'lottery' => $group->lottery,
                'products' => $products,
                'amount' => $amount,
                'taxAmount' => $taxAmount,
                'total' => $totalAmount,
                'discount' => $totalDiscount,
                'tax' => $groupwithcalc,
                'payment' => $payments
                // 'day' => $day
            ];
            return $vatData;
        }
        return response()->json([
            'success' => false,
            'message' => 'Хүсэлт амжилтгүй боллоо.'
        ], 200);
    }

    public function print(Request $request)
    {
        // Get token from artlab
        $token = $this->login();
        // seperate token
        if (substr($token, 0, 7) === "Bearer ") {
            $token = substr($token, 7);
        }
        // find group reservations
        $group = Group::find($request->id);
        // check group reservation exist
        if (!is_null($group)) {
            if (is_null($group->lottery)) {
                $reservations = $group->reservations;
                // params
                // $products = collect([[
                //     'code' => $group->id
                // ]]);
                $payments = array();
                $dtlList = collect();
                foreach ($reservations as $reservation) {
                    $value = 0;
                    $discount = 0;
                    $roomTypeName = $reservation->roomTypeClone->name;
                    // $day = count($reservation->dayRates);
                    foreach ($reservation->dayRates as $dayRate) {
                        $value += $dayRate->value;
                    }
                    if ($reservation->discount_type === 'currency') {
                        $discount = $reservation->discount;
                    } else {
                        $discount = ($value * $reservation->discount) / (100 - $reservation->discount);
                    }
                    $cash = 0;
                    $card = 0;
                    foreach ($reservation->payments as $payment) {
                        $pay = json_decode($payment->income_pays);
                        if ($pay[0]->payment_method === 'Бэлэн мөнгө') {
                            $cash += $payment->amount;
                        } else {
                            $card += $payment->amount;
                        }
                    }
                    $amount = $card + $cash;
                    if ($cash === 0 && $card === 0) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Төлбөрийн бүртгэл үүсээгүй байна.'
                        ], 200);
                    } elseif ($amount !== $reservation->amount) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Захиалга төлбөрийн үлдэгдэлтэй байна.'
                        ], 200);
                    }
                    if ($cash !== 0) {
                        $payments = array_add($payments, 'cash', $cash);
                    }
                    if ($card !== 0) {
                        $payments = array_add($payments, 'card', $card);
                    }
                    // $tax = (($value + $discount) * $reservation->taxPercentage()) / 100;
                    $product = [
                        'code' => $reservation->roomTypeClone->roomType->id,
                        'qty' => 1,
                        // 'price' => $value + $discount + $tax,
                        'price' => $value + $discount,
                        'disc' => $discount
                    ];
                    $dtlList->push($product);
                    $items = [];
                    foreach ($reservation->items as $item) {
                        // $tax = ($item->price * $reservation->taxPercentage()) / 100;
                        $items = [
                            'code' => $item->serviceClone->service->id,
                            'qty' => $item->quantity,
                            'price' => $item->price
                            // 'price' => $item->price + $tax
                        ];
                        $dtlList->push($items);
                    }
                }
                $posBillData = [
                    'id' => $group->id,
                    'payment' => $payments,
                    'dtlList' => $dtlList
                ];

                $response = $this->posBill($posBillData, $token);

                if (json_decode($response['successful'])) {
                    $group->vat_id = json_decode($response['value']);
                    $group->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Хүсэлт амжилтгүй боллоо.'
                    ], 200);
                }

                $vatParams = [];
                if ($request->has('type') && $request->has('regNum')) {
                    $vatParams = [
                        'type' => $request->input('type'),
                        'regNum' => $request->input('regNum')
                    ];
                }
                $response = $this->toVatps($vatParams, $group->vat_id, $token);
                if ($response['successful']) {
                    $group->qr = $response['value']['qrData'];
                    $group->ddtd = $response['value']['billId'];
                    $group->lottery = $response['value']['lottery'];
                    $group->save();
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Хүсэлт амжилтгүй боллоо.'
                    ], 200);
                }

                $response = $this->vatData($request->id);
                return response()->json([
                    'success' => true,
                    'message' => 'Хүсэлт амжилттай.',
                    'data' => $response
                ], 200);
            } else {
                $response = $this->vatData($request->id);
                return response()->json([
                    'success' => true,
                    'message' => 'Хүсэлт амжилттай.',
                    'data' => $response
                ], 200);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Хүсэлт амжилтгүй боллоо.'
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Үйлдэл амжилттай.'
        ], 200);
    }
}
