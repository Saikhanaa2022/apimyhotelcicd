<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Currency, CurrencyClone, Reservation, Hotel, FacilityCategory, Cancellation,
    CancellationPolicyClone, DayRate, Guest, GuestClone, Payment, PaymentMethod,
    PaymentMethodClone, PaymentPay, Group, RoomClone, RoomTypeClone, RatePlanClone,
    SourceClone, TaxClone, User, UserClone, ReservedRoomType
};
use App\Events\{ReservationCreated};

class ResReqController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\ResReq';
    protected $request = 'App\Http\Requests\ResReqRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function filter($query, $request)
    {
        // Get filter type from request
        if ($request->filled('filterType')) {
            $filterType = $request->input('filterType');
            if ($filterType === 'createdAt') {
                $filterField = 'created_at';
            } else if ($filterType === 'checkIn') {
                $filterField = 'check_in';
            } else if ($filterType === 'checkOut') {
                $filterField = 'check_out';
            } else if ($filterType === 'paidAt') {
                $filterField = 'paid_at';
            } else {
                $filterField = '';
            }

            $hasStartDate = $request->filled('startDate');
            $hasEndDate = $request->filled('endDate');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            if ($hasStartDate && $hasEndDate) {
                $query
                    ->whereDate($filterField, '>=', date($startDate))
                    ->whereDate($filterField, '<=', date($endDate));
            } else if ($hasStartDate && !$hasEndDate) {
                $query->whereDate($filterField, '>=', date($startDate));
            } else if (!$hasStartDate && $hasEndDate) {
                $query->whereDate($filterField, '<=', date($endDate));
            }
        }

        if ($request->filled('statuses')) {
            $query->whereIn('status', $request->input('statuses'));
        }

        return $query;
    }

    /**
     * Check reservation request is availabile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $checkResult = $this->checkIsAvailable($request);

        return response()->json([
                'success' => $checkResult['success'],
                'isAvailable' => $checkResult['isAvailable'],
                'message' => $checkResult['message']
            ], $checkResult['success'] ? 200 : 400);
    }

    /**
     * Select date of reservation request.
     * Then create main reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectDate(Request $request)
    {
        // Get data from request
        $id = $request->id;
        $checkIn = $request->checkIn;
        $checkOut = $request->checkOut;

        try {
            // Get reservation request by id
            $resReq = $this->model::find($id);

            // Check reservation request
            if (!is_null($resReq)) {
                // Check already selected
                if ($resReq->status != 'reservation' && $resReq->is_paid) {
                    // Check availability
                    $checkResult = $this->checkIsAvailable($request, $resReq);

                    if ($checkResult['isAvailable'] === false) {
                        return response()->json([
                                'success' => false,
                                'isAvailable' => false,
                                'message' => $checkResult['message']
                            ], 400);
                    }

                    // Create reservation
                    $result = $this->createResFromReq(
                        $request,
                        [
                            'checkIn' => $checkIn,
                            'checkOut' => $checkOut,
                            'guest' => json_decode($resReq->guest),
                        ],
                        $resReq
                    );

                    // Check reservations is created
                    if ($result['status']) {
                        // Update status and dates
                        $resReq->check_in = $checkIn;
                        $resReq->check_out = $checkOut;
                        $resReq->status = 'reservation';
                        $resReq->update();
                    }

                    return response()->json([
                        'success' => $result['status'],
                        'syncId' => $result['status'] ? $result['syncId'] : null,
                        'message' => $result['message']
                    ], $result['status'] ? 200 : 400);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Үйлдэл амжилтгүй. Захиалга өдөр сонгох боломжгүй байна.'
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Үйлдэл амжилтгүй. Захиалга бүртгэгдээгүй байна.'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Үйлдэл амжилтгүй. Алдаа гарлаа.',
            ], 400);
        }
    }

    /**
     * Check reservation request is availables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Array
     */
    private function checkIsAvailable(Request $request, $object = null)
    {
        // Get request data
        $id = $request->id;
        $checkIn = $request->checkIn;
        $checkOut = $request->checkOut;

        $isAvailable = true;
        // Get reservation request by id
        $resReq = is_null($object) ? $this->model::find($id) : $object;

        // Check reservation request room types availability
        if (!is_null($resReq)) {
            $roomTypesIds = $resReq->reservedRoomTypes->pluck('room_type_id');

            // Get roomTypes with available rooms count
            $roomTypes = $request->hotel->roomTypes()
                ->whereIn('id', $roomTypesIds)
                ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
                    $query->unassigned($checkIn, $checkOut);
                }])
                ->get();

            foreach ($roomTypes as $roomType) {
                // Get reserved room type
                $rrt = $resReq
                    ->reservedRoomTypes()
                    ->where('room_type_id', $roomType->id)
                    ->first();

                // Check reserved count for needed
                if ($roomType->rooms_count < $rrt->quantity) {
                    $isAvailable = false;
                }
            }

            // Check room types then define availability
            $isAvailable = count($roomTypes) > 0 ? $isAvailable : false;

            $message = $checkIn . ' ~ ' . $checkOut . ' хооронд байрлах ';

            return [
                'success' => true,
                'isAvailable' => $isAvailable,
                'message' => $message . ($isAvailable ? 'боломжтой байна. Захиалга хадгалах уу?' : 'боломжгүй байна. Өөр өдөр сонгоно уу.')
            ];
        }

        return [
            'success' => false,
            'isAvailable' => false,
            'message' => 'Үйлдэл амжилтгүй. Захиалга бүртгэгдээгүй байна.'
        ];
    }

    /**
     * Create reservation from request
     * Then create main reservation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $hotel
     * @return \Illuminate\Http\Array
     */
    private function createResFromReq($request, $data, $object = null) {
        // Get data
        $checkIn = $data['checkIn'];
        $checkOut = $data['checkOut'];
        $reqGuest = $data['guest'];

        $rooms = $object->reservedRoomTypes;
        $hotel = $object->hotel;

        try {
            // MySQL transaction
            DB::beginTransaction();

            // Find default user
            $user = $hotel->users()->where('is_default', true)->first();

            // Find online book source
            $source = $hotel->sources()
                ->where('is_active', true)
                ->where('service_name', 'ihotel')
                ->first();

            // Calculate stay nights
            $nights = stayNights($checkIn, $checkOut);

            // Create user clone
            $userClone = UserClone::create([
                'name' => $user->name,
                'position' => $user->position,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);

            // Create source clone
            $sourceClone = SourceClone::create([
                'name' => $source->name,
                'short_name' => $source->short_name,
                'color' => $source->color,
                'is_default' => $source->is_default,
                'is_active' => $source->is_active,
                'service_name' => $source->service_name,
                'source_id' => $source->id,
            ]);

            // Find or Create primary guest
            $guest = Guest::firstOrCreate(
                [
                    'email' => $reqGuest->email,
                    'phone_number' => $reqGuest->phone_number,
                    'hotel_id' => $hotel->id,
                ],
                [
                    'name' => $reqGuest->name,
                    'surname' => $reqGuest->surname,
                    'phone_number' => $reqGuest->phone_number,
                    'email' => $reqGuest->email,
                    'passport_number' => $reqGuest->passport_number,
                    'nationality' => $reqGuest->nationality,
                    'is_blacklist' => false,
                    'hotel_id' => $hotel->id,
                ]);

            // Create group
            $group = Group::create(['number' => Group::generateUnique(), 'hotel_id' => $hotel->id]);

            $params = [
                'hotelId' => $hotel->id,
                'userCloneId' => $userClone->id,
                'sourceCloneId' => $sourceClone->id,
            ];
            
            // Check room counts
            if (count($rooms) > 0) {
                // Create reservations
                foreach ($rooms as $item) {
                    // Find original roomType
                    $roomType = $item->roomType;
                    $quantity = $item->quantity;

                    // Check roomType
                    if ($roomType) {
                        if ($quantity > 0) {
                            $numberOfGuests = $item->number_of_guests;
                            $lastGuestsCount = 0;

                            // Find last reservation number of guests
                            if ($numberOfGuests / $quantity > 0) {
                                $lastGuestsCount = $numberOfGuests - ($item->occupancy * ($quantity - 1));
                            }

                            for ($i = 0; $i < $quantity; $i++) {
                                // Create room type clone
                                $roomTypeClone = RoomTypeClone::create([
                                    'name' => $roomType->name,
                                    'sync_id' => $roomType->sync_id,
                                    'short_name' => $roomType->short_name,
                                    'occupancy' => $roomType->occupancy,
                                    'default_price' => $roomType->default_price,
                                    'price_day_use' => $roomType->price_day_use,
                                    'has_time' => $roomType->has_time,
                                    'occupancy_children' => $roomType->occupancy_children,
                                    'has_extra_bed' => $roomType->has_extra_bed,
                                    'extra_beds' => $roomType->extra_beds,
                                    'room_type_id' => $roomType->id,
                                    'discount_percent' => $roomType->discount_percent,
                                    'is_res_request' => $roomType->is_res_request,
                                    'by_person' => $roomType->by_person,
                                    'sale_quantity' => $roomType->sale_quantity,
                                ]);

                                // Rates
                                $rates = [];

                                $checkRoomTypeId = $item->sync_id;

                                // Find block id
                                // $createdBlock = collect($blockSyncIds)->first(function ($item) use ($checkRoomTypeId) {
                                //     return $item['room_id'] == $checkRoomTypeId;
                                // });

                                // $blockSyncId = $createdBlock['id'];
                                $prices = json_decode($item->rates);

                                $guestsCount = $item->occupancy;
                                if ($i == $quantity - 1 &&  $lastGuestsCount > 0) {
                                    $guestsCount = $lastGuestsCount;
                                }

                                // Push rates
                                foreach ($prices as $price) {
                                    if ($item->by_person) {
                                        $value = (int) (($price->amount / $numberOfGuests) * $guestsCount);
                                    } else {
                                        $value = (int) ($price->amount / $quantity);
                                    }
                                    $start = Carbon::parse($checkIn)->addDays($price->dayNumber)->format('Y-m-d');
                                    array_push($rates, ['id' => null, 'date' => $start, 'value' => $value]);
                                }

                                // Total amount of rates
                                $amount = array_sum(array_column($rates, 'value'));

                                $data = array_merge($params, [
                                    // 'sync_id' => $blockSyncId,
                                    'stayType' => 'night',
                                    'res_req_id' => $object->id,
                                    'status' => 'confirmed',
                                    'amount' => $amount,
                                    'number' => Reservation::generateNumber(),
                                    'numberOfGuests' => $guestsCount,
                                    'numberOfChildren' => 0,
                                    'ratePlanCloneId' => null,
                                    'roomTypeCloneId' => $roomTypeClone->id,
                                    'groupId' => $group->id,
                                    'checkIn' => $checkIn . ' ' . $hotel->check_in_time,
                                    'checkOut' => $checkOut . ' ' . $hotel->check_out_time,
                                    'arrivalTime' => $hotel->check_in_time,
                                    'exitTime' => $hotel->check_out_time,
                                    'statusAt' => Carbon::now(),
                                    'postedDate' => $hotel->working_date,
                                ]);

                                // Store new reservation
                                $reservation = Reservation::create(snakeCaseKeys($data));

                                // Create day rates
                                foreach ($rates as $rate) {
                                    $value = $rate['value'];
                                    DayRate::create([
                                        'date' => $rate['date'],
                                        'value' => $value,
                                        'default_value' => $value,
                                        'reservation_id' => $reservation->id,
                                    ]);
                                }

                                // Create guest clone
                                GuestClone::create([
                                    'name' => $guest->name,
                                    'surname' => $guest->surname,
                                    'phone_number' => $guest->phone_number,
                                    'email' => $guest->email,
                                    'passport_number' => $guest->passport_number,
                                    'nationality' => $guest->nationality,
                                    'description' => $guest->description,
                                    'is_blacklist' => $guest->is_blacklist,
                                    'blacklist_reason' => $guest->blacklist_reason,
                                    'guest_id' => $guest->id,
                                    'reservation_id' => $reservation->id,
                                    'is_primary' => true,
                                ]);

                                $reservation->save();

                                // Check is auto arrange room
                                // if ($hotel->is_auto_arrange) {
                                $availableRooms = $reservation->availableRooms();
                                // Get first room
                                $firstRoom = $availableRooms->first();

                                // Create room clone
                                if ($firstRoom) {
                                    $roomClone = RoomClone::create([
                                        'name' => $firstRoom->name,
                                        'ic_name' => $firstRoom->ic_name,
                                        'status' => $firstRoom->status,
                                        'description' => $firstRoom->description,
                                        'room_id' => $firstRoom->id,
                                    ]);

                                    $reservation->roomClone()->associate($roomClone);
                                    $reservation->save();
                                }
                                // }

                                // Sync taxes to reservation taxes
                                foreach ($hotel->taxes as $tax) {
                                    if ($tax->is_enabled) {
                                        // Create tax clones for reservation
                                        $data = TaxClone::create([
                                            'name' => $tax->name,
                                            'percentage' => $tax->percentage,
                                            'inclusive' => true,
                                            'key' => $tax->key,
                                            'is_default' => $tax->is_default,
                                            'is_enabled' => $tax->is_enabled,
                                            'reservation_id' => $reservation->id,
                                            'tax_id' => $tax->id,
                                        ]);
                                        // if (!$data->inclusive) {
                                        //     // Update reservation amount
                                        //     $reservation = $data->reservation;
                                        //     $reservation->update([
                                        //         'amount' => $reservation->calculate(),
                                        //     ]);
                                        // }
                                    }
                                }

                                // Create payment for reservation
                                // Check reservation request is paid
                                if ($object->is_paid) {
                                    // Find original currency
                                    $currency = $hotel->currencies()->where('is_default', true)->first();

                                    // Create currency clone
                                    $currencyClone = CurrencyClone::create([
                                        'name' => $currency->name,
                                        'short_name' => $currency->short_name,
                                        'rate' => $currency->rate,
                                        'is_default' => $currency->is_default,
                                        'currency_id' => $currency->id,
                                    ]);

                                    $incomeType = 'prepaid';

                                    // Create payment
                                    $payment = Payment::create([
                                        'amount' => $reservation->amount,
                                        'posted_date' => Carbon::today(), 
                                        'income_type' => $incomeType,
                                        'notes' => 'Урьдчилсан захиалга',
                                        'bill_type' => NULL,
                                        'reservation_id' => $reservation->id,
                                        'user_clone_id' => $userClone->id,
                                        'currency_clone_id' => $currencyClone->id,
                                    ]);

                                    // Create payment pays
                                    // Find original payment method
                                    $paymentMethod = $hotel->paymentMethods()->where('is_default', true)->first();
                                    
                                    // Create payment method clone
                                    $paymentMethodClone = PaymentMethodClone::create([
                                        'name' => $paymentMethod->name,
                                        'color' => $paymentMethod->color,
                                        'is_default' => $paymentMethod->is_default,
                                        'income_types' => $paymentMethod->income_types,
                                        'is_paid' => $paymentMethod->is_paid,
                                        'payment_method_id' => $paymentMethod->id,
                                    ]);

                                    // Create payment pays
                                    PaymentPay::create([
                                        'payment_id' => $payment->id,
                                        'payment_method_clone_id' => $paymentMethodClone->id,
                                        'amount' => $reservation->amount,
                                    ]);
                                    /**********************************************************/

                                    $guestClone = $reservation->guestClone;
                                    $pays = [];

                                    foreach ($payment->pays as $pay) {
                                        array_push($pays, [
                                            'id' => $pay->id,
                                            'amount' => $pay->amount,
                                            'payment_method' => $pay->paymentMethodClone->name,
                                            'payment_method_clone_id' => $pay->payment_method_clone_id
                                        ]);
                                    }

                                    $payment->income_pays = json_encode($pays, JSON_UNESCAPED_UNICODE);
                                    $payment->paid_at = Carbon::now();
                                    $payment->is_active = true;
                                    $payment->payer = json_encode([
                                            'name' => $guestClone->name,
                                            'surname' => $guestClone->surname,
                                            'phone_number' => $guestClone->phone_number,
                                            'email' => $guestClone->email,
                                            'passport_number' => $guestClone->passport_number,
                                        ], JSON_UNESCAPED_UNICODE);
                                    $payment->update();

                                    // Update paid amount of reservation
                                    $reservation->amount_paid = $reservation->calcPaidAmount();
                                    $reservation->save();
                                }
                            }
                        } else {
                            return [
                                'status' => false,
                                'message' => 'Алдаа гарлаа. Өрөөний тоо хэмжээ буруу байна.',
                            ];
                        }
                    } else {
                        return [
                            'status' => false,
                            'message' => 'Алдаа гарлаа. Өрөөний бүртгэл олдсонгүй.',
                        ];
                    }
                }

                // Commit transaction
                DB::commit();

                // Check is sync
                if ($hotel->has_ihotel && !is_null($hotel->sync_id)) {
                    // Sync reservations
                    event(new ReservationCreated($group, new User, false, 'resReq', ['syncId' => $object->id, 'checkIn' => $checkIn, 'checkOut' => $checkOut]));
                }

                return [
                    'status' => true,
                    'syncId' => $group->id,
                    'message' => 'Захиалга өдөр сонголт амжилттай бүртгэгдлээ.'
                ];
            } else {
                return [
                    'status' => false,
                    'message' => 'Өрөөний мэдээлэл алдаатай байна.'
                ];
            }
        }  catch (Exception $e) {
            DB::rollBack();

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
