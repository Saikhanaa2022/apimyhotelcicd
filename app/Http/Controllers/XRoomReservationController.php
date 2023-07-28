<?php

namespace App\Http\Controllers;

use App\Events\XRoomInvoiceConfirmed;
use App\Models\DailyRate;
use App\Models\Hotel;
use App\Models\Notification;
use App\Models\XRoomConfig;
use App\Models\XRoomReservation;
use App\Services\QPayService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use \GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class XRoomReservationController extends Controller
{
    //
    private $qpayService;
    private $reservationService;

    public function __construct(
        QPayService $qpayService,
        ReservationService $reservationService
    ) {
        $this->qpayService = $qpayService;
        $this->reservationService = $reservationService;
    }

    public function init()
    {
        $configs = XRoomConfig::all();

        return response()->json([
            'configs' => $configs
        ]);
    }

    public function checkCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stayType' => 'required|in:day,night',
            'hotelId' => 'required|integer',
            'roomTypeId' => 'required|integer',
            'code' => 'required|digits:4'
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validate your data',
                    'errors' => $validator->errors()
                ]
            );
        }

        $hotelId = $request->input('hotelId');
        $stayType = $request->input('stayType');
        $roomTypeId = $request->input('roomTypeId');
        $code = $request->input('code');

        $availableCount = $this->reservationService->checkAvailableCount($request->input('roomTypeId'), $stayType);

        $date = Carbon::now()->subMinutes(config('constants.date.expire'))->format('Y-m-d H:i');

        $pendingCount = XRoomReservation::where('room_type_id', $roomTypeId)
            ->where('stay_type', $stayType)
            ->where('payment_status', 'pending')
            ->where('created_at', '>', $date) // older than 2 hour invoices are expired
            ->count();
        Log::info('pending: ' . $pendingCount);
        $realAvailableCount = $availableCount - $pendingCount;
        Log::info('real: ' . $pendingCount);
        if ($realAvailableCount <= 0) {
            return response()->json([
                'success' => true,
                'isAvailable' => false,
                'isCodeAvailable' => false
            ]);
        }

        $codeCountInHotel = XRoomReservation::where('hotel_id', $hotelId)
            ->where('stay_type', $stayType)
            ->where('created_at', '>', $date)
            ->where('code', $code)->count();

        return response()->json([
            'success' => true,
            'isAvailable' => true,
            'isCodeAvailable' => $codeCountInHotel == 0,
        ]);
    }

    public function createInvoice(Request $request)
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'hotelId' => 'required|integer',
                'roomTypeId' => 'required|integer',
                'clientId' => 'required',
                'paymentMethod' => 'required|string|in:qpay,socialpay',
                'stayType' => 'required|string|in:day,night',
                'code' => 'required|digits:4'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validate your data',
                        'errors' => $validator->errors()
                    ]
                );
            }

            if ($request->input('paymentMethod') === 'socialpay') {
                return response()->json([
                    'success' => true,
                    'message' => 'Coming soon...'
                ]);
            }

            $stayType = $request->input('stayType');

            $configs = XRoomConfig::whereIn('code', ['servicefee', 'checkin', 'checkout', 'daystart'])->get();

            if (count($configs) < 4) {
                Log::error('XRoom configuration misconfigured or not found.');
                return response()->json([
                    'success' => false,
                    'code' => 'CONFIG',
                    'message' => 'Сервер дээр алдаа гарсан байна.'
                ], 500);
            }

            $checkInHour = (int) $configs->firstWhere('code', 'checkin')['value'];
            $checkOutHour = (int) $configs->firstWhere('code', 'checkout')['value'];
            $fee = (float) $configs->firstWhere('code', 'servicefee')['value'];
            $dayStart = (int) $configs->firstWhere('code', 'daystart')['value'];

            $now = Carbon::now();

            $checkIn = Carbon::now()->hour($checkInHour)->minute(0);
            $checkOut = Carbon::now()->hour($checkOutHour)->minute(0);

            if ($stayType == "night" && $now->hour > 0 && $now->hour <= $dayStart) {
                $checkIn = $checkIn->addDays(-1);
                $checkOut = $checkOut->addDays(-1);
            }

            $checkIn = $checkIn->format(config('constants.date.default'));
            $checkOut = $checkOut->format(config('constants.date.default'));

            if ($stayType == "day" && $now->hour >= 0 && $now->hour < $dayStart) {
                return response()->json([
                    'success' => false,
                    'message' => 'Өдрийн захиалга эхлэх болоогүй байна.'
                ], 400);
            }

            if ($stayType == "day" && $now->hour >= $checkOutHour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Өдрийн захиалга авах цаг дууссан байна.'
                ], 400);
            }

            if ($stayType === "night") {
                $oldCheckIn = $checkIn;
                $checkIn = $checkOut;
                $checkOut = Carbon::parse($oldCheckIn)->addDay(1)->format(config('constants.date.default'));
            }

            $totalAmount = 0;
            $description = 'Xroom захиалга';

            if (Carbon::parse($checkIn)->greaterThanOrEqualTo(Carbon::parse($checkOut)) || stayNights($checkIn, $checkOut, true) < 1) {
                info('checkin: ' . $checkIn);
                info('checkout: ' . $checkOut);
                return response()->json([
                    'success' => false,
                    'message' => config('constants.log.date')
                ], 400);
            }

            $hotel = Hotel::find($request->input('hotelId'));

            if ($hotel === NULL) {
                return response()->json([
                    'success' => false,
                    'message' => 'Буудлын мэдээлэл олдсонгүй.',
                ], 400);
            }

            $roomType = $hotel->roomTypes()
                ->where('id', $request->input('roomTypeId'))
                ->first();

            $totalAmount = $roomType->default_price;

            $date_str = Carbon::now()->format('Y-m-d');

            $dailyRate = DailyRate::whereHas('ratePlan', function ($query) use ($roomType) {
                $query->where('room_type_id', $roomType->id)
                    ->where('is_online_book', 1);
            })
                ->where('date', $date_str)
                ->first();

            if ($dailyRate != null) {
                $totalAmount = $dailyRate->value;
            }

            if ($stayType === 'day') {
                $totalAmount = $roomType->price_day_use;
            }

            $clientId = $request->input('clientId');

            $reservation = XRoomReservation::create(snakeCaseKeys($request->all(['stayType', 'paymentMethod', 'hotelId', 'roomTypeId', 'clientId', 'code'])));
            $reservation->amount = $totalAmount;
            $reservation->fee = $fee;

            $reservation->check_in = $checkIn;
            $reservation->check_out = $checkOut;
            $reservation->client_id = $clientId;

            $reservation->save();

            $paymentMethod = $request->input('paymentMethod');

            if ($paymentMethod === 'qpay') {
                $totalAmountWithFee = $totalAmount + $fee;
                $qpay = $this->qpayService->createInvoice($totalAmountWithFee, $reservation->id, $description, $clientId);
                $decoded = json_decode($qpay);
                $reservation->invoice_no = $decoded->invoice_id;
                $reservation->invoice_data = $decoded;
            }

            $reservation->save();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'order' => $reservation,
                'qpay' => $decoded
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => '' . $e->getMessage(),
                'message' => config('constants.log.error'),
            ], 400);
        }
    }

    function qpayConfirm(Request $request)
    {
        $invoice_no = $request->input('invoice_no');
        Log::info('[' . $invoice_no . '] starting');

        if (is_null($invoice_no)) {
            return response()->json([
                'success' => false,
                'message' => config('constants.log.error')
            ], 400);
        }

        $reservation = XRoomReservation::find($invoice_no);

        if (is_null($reservation)) {
            return response()->json([
                'success' => false,
                'message' => 'Reservation not found.'
            ], 400);
        }

        if ($reservation->payment_method !== 'qpay') {
            return response()->json([
                'success' => false,
                'message' => 'Reservation is wrong.'
            ], 400);
        }

        // $reservation->res_id = $response->getData()['resId'];
        $reservation->invoice_data = null;
        $reservation->payment_status = 'confirmed';
        $reservation->save();

        event(new XRoomInvoiceConfirmed($reservation));

        $hotel = Hotel::find($reservation->hotel_id);
        $user = $hotel->users()->first();
        $roomType = $hotel->roomTypes()
                ->where('id', $reservation->room_type_id)
                ->first();

        $http = new Client;
        $response = $http->post('https://onesignal.com/api/v1/notifications', [
            'json' => [
                'app_id' => "c36087dc-5994-49c7-8b61-247204bd9a43",
                'name' => "notification",
                'include_external_user_ids' => ["$user->id"],
                'data' => ["reservationId" => "$reservation->id","hotelId" => "$hotel->id","xroom" => true ],
                'headings' => ["en" => "Xroom сувгаас захиалга үүслээ"],
                'contents' => ["en" => "$hotel->name $roomType->name өрөөнд $reservation->code дугаартай захиалга баталгаажлаа."],
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ZTRjYzVkNzQtOGE0MS00ZjNhLWFkODktNWJhOGFjNzNlODM4'
            ]
        ]);

        $jsonData = $response->getBody()->getContents();
        $phpData = json_decode($jsonData)->id;
        Log::info($jsonData);
        Log::info($phpData);
        if($phpData){
            Notification::create([
                'id' => $phpData,
                'type' => 'App\Notification\XroomNotification',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $user->id,
                'data' => ['message' => "$hotel->name $roomType->name өрөөнд $reservation->code дугаартай захиалга баталгаажлаа.", 'hotelId' => $hotel->id, 'event'=> 'created', 'type' => 'newReservatonXroom', 'dataId' => '', 'reservationId' => $reservation->id, 'xroom' => true, 'title' => 'Xroom сувгаас захиалга үүслээ']
            ]);
        }
        
        
        // $viewNotification = $http->get('https://onesignal.com/api/v1/notifications/'.$phpData.'?app_id=c36087dc-5994-49c7-8b61-247204bd9a43', [
        //     'headers' => [
        //         'Authorization' => 'Basic ZTRjYzVkNzQtOGE0MS00ZjNhLWFkODktNWJhOGFjNzNlODM4'
        //     ]
        // ]);
       
      

        info('[' . $invoice_no . '] confirmed');

        return response()->json([
            'success' => true,
            'message' => 'Payment confirmed for [' . $invoice_no . ']'
        ]);
    }

    public function checkPaymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validate your data',
                    'errors' => $validator->errors()
                ]
            );
        }

        $id = $request->input('id');
        $reservation = XRoomReservation::find($id);

        if (is_null($reservation)) {
            return response()->json([
                'success' => false,
                'message' => 'invoice not found'
            ], 400);
        }

        $status = $reservation->payment_status;

        return response()->json([
            'success' => true,
            'payment_status' => $status
        ]);
    }
}