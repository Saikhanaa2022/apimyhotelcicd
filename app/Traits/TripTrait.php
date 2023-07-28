<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait TripTrait
{
    public function hotelTrait($hotel) 
    {
        $languageCode = 'en-US';
        $sendData['languageCode'] = $languageCode;
        $sendData['hotelCode'] = '' . $hotel->sync_id;
        $sendData['active'] = $hotel->is_active ? true : false;

        $sendData['hotelBasicInfo']['hotelNames'][] = [
            'languageCode' => $languageCode,
            'content' => $hotel->name_en
        ];

        $sendData['hotelBasicInfo']['hotelDescriptions'][] = [
            'languageCode' => $languageCode,
            'content' => $hotel->introduction_en
        ];

        $sendData['hotelBasicInfo']['currency'] = 'USD';

        $s = trim($hotel->location, '[]');
        $coords = explode(",", $s);
        $sendData['hotelBasicInfo']['positions'][] = [
            'source' => 'google',
            'latitude' => str_replace('"', '', $coords[0]),
            'longitude' => str_replace('"', '', $coords[1])
        ];

        $sendData['hotelBasicInfo']['addresses'][] = [
            'languageCode' => $languageCode,
            'country' => 'Mongolia',
            'province' => $hotel->city->name_en,
            'address' => $hotel->address_en
        ];

        $sendData['hotelBasicInfo']['addressVisible'] = true;

        $phones = explode(',', $hotel->phone_number);

        foreach($phones as $phone) {
            $pd = [
                'phoneType' => 'Phone',
                'phoneNumber' => [
                    'countryCode' => 976,
                    'mainCode' => $phone
                ]
            ];
            $sendData['hotelBasicInfo']['phones'][] = $pd;
        }

        $sendData['hotelBasicInfo']['emails'][] = [
            'emailType' => 'Reservation',
            'email' => $hotel->email_book
        ];

        $sendData['hotelBasicInfo']['totalRoomQuantity'] = $hotel->rooms_count;

        foreach ($hotel->services as $service) {
            $temp['facilityName'] = $service->name_en;
            $temp['facilityId'] = $service->id;
            $sendData['hotelFacilities'][] = $temp;
        }

        // Get images from ihotel.mn
        $sendData['hotelImages'][] = [
            'imageUrl' => 'https://ihotel.mn/files/' . $hotel->cover_photo,
            'imageCategoryId' => '',
            'imageCategory' => ''
        ];

        $photos = unserialize($hotel->other_photos);

        foreach($photos as $p) {
            $image['imageUrl'] = 'https://ihotel.mn/files/' . $p;
            $image['imageCategoryId'] = '';
            $image['imageCategory'] = '';
            $sendData['hotelImages'][] = $image;
        }
        return $sendData;
    }

    public function success($hotel)
    {
        return [
            'status' => 'Success',
            'hotelCode' => (string)$hotel->id,
            'hotelName' => $hotel->name,
            'currency' => $hotel->currency
        ];
    }

    public function failure($hotel)
    {
        return [
            'status' => 'Success',
            'hotelCode' => (string)$hotel->id,
            'hotelName' => $hotel->name,
            'currency' => 'USD'
        ];
    }

    public function noRoom($hotel)
    {
        return [
            'status' => 'NoRoom',
            'hotelCode' => (string)$hotel->id,
            'hotelName' => $hotel->name,
            'currency' => 'USD',
            'rooms' => []
        ];
    }

    public function fail($hotel)
    {
        return [
            'status' => 'Failure',
            'hotelCode' => (string)$hotel->id,
            'code' => 'CB1005',
            'message' => 'Request data invalid, [hotelCode: ' . $hotel->id . ']'
        ];
    }

    public function roomType($r, $checkIn, $checkOut, $taxPercent, $usd, $numberOfUnits) 
    {
        $nights = stayNights($checkIn, $checkOut, false);
        // $r->default_price = $r->default_price * $numberOfUnits;

        // Calculate new price
        $rates = [];
        $childAmount = 0;
        if ($r->children_policy) {
            $childAmount = $r->children_policy->price;
            if ($r->children_policy->price_type === 'percent') {
                $childAmount = $r->default_price / 100 * $r->children_policy->price;
            }
        }

        foreach($r->ratePlans as $rp) {
            $dailyRates = [];
            $policies;
            $data['ratePlanCode'] = (string)$rp->id;
            $data['ratePlanDescription'] = $rp->name;
            $data['adults'] = $r->occupancy;
            $data['childrenCount'] = $r->occupancy_children ? $r->occupancy_children : 0;
            $policy = $r->policy;
            $data['cancellationPolicies'][] = $policy;
            // $data['rateBeforeTaxTotal'] = $this->convertCurrency(($nights * $numberOfUnits * $r->default_price) / $usd);

            // $data['rateAfterTaxTotal'] = $this->convertCurrency(($nights * $numberOfUnits * ($r->default_price + calculatePercent($r->default_price, $taxPercent))) / $usd);
            $data['rateBeforeTaxTotal'] = 0;
            $data['rateAfterTaxTotal'] = 0;
            $data['mealType'] = 0;
            // $policies[] = $this->computePolicy($r, $policy, $r->default_price, $usd, $checkIn, $checkOut);

            $data['rateCategory'] = 'Prepay';
            for ($i = 0; $i < $nights; $i++) {
                $start = Carbon::parse($checkIn)->addDays($i)->format('Y-m-d');
                $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
                $rate = $rp->getDailyRate($start, $end);
                if (!is_null($rate)) {
                    $info = $this->dailyRatePrepare($start, $rate->value, $taxPercent, $usd, $rp->meals); //  $numberOfUnits

                    if(!is_null($info)) {
                        $dailyRates[] = $info;
                    }
                    // $policies[] = $this->computePolicy($r, $policy, $rate->value, $usd, $start, $checkOut); //  $numberOfUnits
                    $data['rateBeforeTaxTotal'] += $numberOfUnits * $this->convertCurrency(($rate->value) / $usd);
                    $data['rateAfterTaxTotal'] += $numberOfUnits * $this->convertCurrency((($rate->value + calculatePercent($rate->value, $taxPercent))) / $usd);
                } else {
                    $info = $this->dailyRatePrepare($start, $r->default_price, $taxPercent, $usd, $rp->meals);
                    $data['rateBeforeTaxTotal'] += $numberOfUnits * $this->convertCurrency(($r->default_price) / $usd);
                    $data['rateAfterTaxTotal'] += $numberOfUnits * $this->convertCurrency((($r->default_price + calculatePercent($r->default_price, $taxPercent))) / $usd);
                    if(!is_null($info)) {
                        $dailyRates[] = $info;
                    }
                } 
                if($rp->meals->count() > 0) {
                    $data['mealType'] = $this->mealType($rp->meals);
                }
                
            }

            $start = Carbon::parse($checkIn)->format('Y-m-d');
            $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
            $realend = Carbon::parse($checkOut)->format('Y-m-d');
            $rate = $rp->getDailyRate($start, $end);
            if (!is_null($rate)) {
                //$policies[] = $this->computePenaltyPercent($data['rateAfterTaxTotal'], $policy, $this->convertCurrency(($rate->value * $numberOfUnits)/ $usd), $start, $end);
                $policies = $this->computePenaltyAmount(($r->default_price), $policy, ($rate->value), $usd, $start, $realend, $numberOfUnits);
            } else {
                //$policies[] = $this->computePenaltyPercent($data['rateAfterTaxTotal'], $policy, $this->convertCurrency(($r->default_price * $numberOfUnits)/ $usd), $start, $end);
                $policies = $this->computePenaltyAmount(($r->default_price), $policy, ($r->default_price), $usd, $start, $realend, $numberOfUnits);
            }

            $data['dailyRates'] = $dailyRates;
            $data['cancellationPolicies'] = $policies;

            $rates[] = $data;
        }

        return [
            'roomTypeCode' => (string)$r->id,
            'roomTypeName' => $r->name,
            'rates' => $rates
        ];
    }

    public function computeRatePlan($r, $rp, $checkIn, $checkOut, $taxPercent, $usd, $numberOfUnits) 
    {
        $nights = stayNights($checkIn, $checkOut, false);
        $dailyRates = [];

        for ($i = 0; $i < $nights; $i++) {
            $start = Carbon::parse($checkIn)->addDays($i)->format('Y-m-d');
            $end = Carbon::parse($start)->addDays(1)->format('Y-m-d');
            $rate = $rp->getDailyRate($start, $end);

            if ($rate) {
                $info = $this->dailyRatePrepare($start, $rate->value, $taxPercent, $usd, $rp->meals); //  $numberOfUnits

                if(!is_null($info)) {
                    $dailyRates[] = $info;
                }
            } else {
                $info = $this->dailyRatePrepare($start, $r->default_price, $taxPercent, $usd, $rp->meals);
                if(!is_null($info)) {
                    $dailyRates[] = $info;
                }
            }
        }

        return [
            'dailyRates' => $dailyRates
        ];
    }

    public function dailyRatePrepare($date, $price, $tax, $usd, $meals) 
    {
        $info['periodStartDate'] = $date;
        $info['periodEndDate'] = $date;
        $info['rateBeforeTax'] = $this->convertCurrency($price / $usd);
        $info['rateAfterTax'] = $this->convertCurrency(($price + calculatePercent($price, $tax)) / $usd);
        $info['mealCount'] = count($meals);
        return $info;
    }

    public function convertCurrency($price) 
    {
        return round($price);//number_format((float)$price, 2, '.', '');//round($price,2);//* 1.01
    }

    public function room($hotel, $rooms, $rate) 
    {
        $languageCode = 'en-US';
        $sendData['languageCode'] = $languageCode;
        $sendData['hotelCode'] = $hotel->id;

        $roomsData = [];

        $adultPolicies = $hotel->extraBedPolicies()
            ->where(function ($query) {
                $query->where('age_type', 'adults')
                    ->orWhere('age_type', 'any');
            })
            ->first();

        foreach($rooms as $room) {
            $roomdata['roomTypeCode'] = $room->sync_id;
            $roomdata['pmsRoomTypeCode'] = $room->sync_id;
            $roomdata['active'] = true;
            $currency = 'USD';

            // BasicInfo
            $roomdata['roomBasicInfo']['currency'] = $currency;
            // Room Names
            $roomdata['roomBasicInfo']['roomNames'][] = [
                'languageCode' => $languageCode,
                'content' => $room->name_en
            ];

            // Description
            $roomdata['roomBasicInfo']['roomDescriptions'][] = [
                'languageCode' => $languageCode,
                'content' => $room->introduction_en
            ];

            // Occupancy
            $roomdata['roomBasicInfo']['occupancy']['maxOccupancy'] = $room->people_number + $room->children;
            $adult['maxAdultOccupancy'] = $room->people_number;

            if ($room->extra_beds) {
                $obj['quantity'] = $room->extra_beds;
                if ($adultPolicies->count() === 1) {
                    $obj['addingBedFee'] = $this->convertCurrency($adultPolicies->price / $rate);
                } else {
                    $obj['addingBedFee'] = 0;
                }
            } else {
                $obj['quantity'] = 0;
                $obj['addingBedFee'] = 0;
            }

            $adult['addBed'] = $obj;
            $roomdata['roomBasicInfo']['occupancy']['adult'] = $adult;

            $children['isAllowChildren'] = $room->children > 0 ? true : false;
            $children['sharingBedChildrenOccupancy'] = $room->children;

            if ($room->children) {
                $object['normalBedNum'] = $room->children;
                $object['infantBedNum'] = 0;
                $object['isOr'] = false;
            } else {
                $object['normalBedNum'] = 0;
                $object['infantBedNum'] = 0;
                $object['isOr'] = false;
            }

            $children['addBed'] = $object;
            $roomdata['roomBasicInfo']['occupancy']['children'] = $children;
            $roomdata['roomBasicInfo']['roomQuantity'] = $room->rooms;
            $smoking = 2;
            foreach($room->amenities as $am) {
                if ($am->id == 10) {
                    $smoking = 1;
                } 
            }
            $roomdata['roomBasicInfo']['smoking'] = $smoking;
            $roomdata['roomBasicInfo']['wifi'] = [
                'available' => $hotel->is_internet ? 1 : 0
            ];
            $roomdata['roomBasicInfo']['cableInternet'] = [
                'available' => 0
            ];
            $roomdata['roomBasicInfo']['window'] = $room->window > 0 ? 2 : 0;
            $roomdata['roomBasicInfo']['area'] = floatval($room->floor_size);

            foreach($room->images as $img) {
                $roomdata['roomImages'][] = [
                    'imageUrl' => 'https://ihotel.mn/' . $img
                ];
            }

            foreach($room->amenities as $am) {
                $roomdata['roomAmenities'][] = [
                    'amenityId' => $am->id,
                    'amenityName' => $am->name_en
                ];
            }
            
            $roomsData[] = $roomdata;
        }
        
        $sendData['roomDatas'] =  $roomsData;
        
        return $sendData;
    }

    public function statusConvert($status) 
    {
        if ($status == 'pending') {
            return 'Pending';
        } else if ($status == 'confirmed' || $status == 'checked-in' || $status == 'checked-out') {
            return 'Reserved';
        } else if ($status == 'canceled') {
            return 'Canceled';
        } else {
            return 'Reserved';
        }
    }

    public function computePolicy($rt, $policy, $price, $usd, $checkIn, $checkOut)
    {
        $cancellationPayment = 0;
        $deadline= false;
        $policy_percent = $policy->cancellationPercent()->first();
        $policyAdditionPercent = $policy->cancellationAdditionPercent()->first();
        $policy_time = $policy->cancellationTime()->first();
        $deadline = $policy_time->has_time ? false : $policy_time->day;
        $parsedCheckIn = Carbon::parse($checkIn);

        // Төлбөр гарсан эсэхийг хугацаан дээрээс тооцоолж мэдэх
        // Хичнээн хэмжээний төлбөр гарсаныг цуцлалтын бодлогоос тооцох
        $dateNow = Carbon::now();
        // Reservation first nigth payment
        $firstNight = $price;
        $cancellationPayment = $firstNight;

        if (!$policy->is_free) {
            if ($policy_time) {
                // Цаг тохриуулагдсан байвал
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day);

                // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
                if ($dateNow->gt($parsedCheckIn)) {
                    // No Show cancellation
                    if ($policyAdditionPercent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = round($price / 100 * $policyAdditionPercent->percent);
                    }
                }
                // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
                if ($parsedFreeDay->gt($dateNow)) {
                    // Free cancellation
                    if ($policy_percent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = round($price / 100 * $policy_percent->percent);
                    }
                }
            } else {
                if (!$policy_percent->is_first_night) {
                    $cancellationPayment = round($rt->default_price / 100 * $policy_percent->percent);
                }
            }
            //$cancellationPayment = $policy_percent->percent;
            return [
                    'penalty' => $this->convertCurrency($cancellationPayment / $usd),
                    'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
            ];
        } else {
            if ($policy_time->has_time) {
                $checkIn = $checkIn . ' ' . $policy_time->day . ':00';
                $parsedFreeDay = Carbon::parse($checkIn);
            } else {
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day + 1);
            }

            // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
            if ($dateNow->gt($parsedCheckIn)) {
                // No Show cancellation
                if ($policyAdditionPercent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = round($price / 100 * $policyAdditionPercent->percent);
                }
            }

            // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
            if ($parsedFreeDay->gt($dateNow)) {
                // Free cancellation
                $cancellationPayment = 0; 
                return $this->freeCancellation($checkOut);
            }

            // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах хугацааг хэтэрсэн мөн ирэх ёстой хугацаанаас өмнө байх тохиолдолд
            // тодорхой заасан төлбөрийг тооцож авна
            if ($parsedCheckIn->gt($parsedFreeDay) && $parsedCheckIn->gt($dateNow)) {
                // First Night cancellation
                if ($policy_percent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = round($price / 100 * $policy_percent->percent);
                }
            }

            return [
                'penalty' => $this->convertCurrency($cancellationPayment / $usd),
                'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
            ];
        }
    }
    public function computePenaltyPercent($totalAfterTax, $policy, $price, $checkIn, $checkOut)
    {
        if($price == 0){
            return $this->freeCancellation($checkOut);
        }

        $cancellationPayment = 0;
        $deadline= false;
        $policy_percent = $policy->cancellationPercent()->first();
        $policyAdditionPercent = $policy->cancellationAdditionPercent()->first();
        $policy_time = $policy->cancellationTime()->first();
        $deadline = $policy_time->has_time ? false : $policy_time->day;
        $parsedCheckIn = Carbon::parse($checkIn);

        // Төлбөр гарсан эсэхийг хугацаан дээрээс тооцоолж мэдэх
        // Хичнээн хэмжээний төлбөр гарсаныг цуцлалтын бодлогоос тооцох
        $dateNow = Carbon::now();
        // Reservation first nigth payment
        $firstNight = round(($price / $totalAfterTax)*100, 2);
        $cancellationPayment = $firstNight;

        if (!$policy->is_free) {
            if ($policy_time) {
                // Цаг тохриуулагдсан байвал
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day);

                // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
                if ($dateNow->gt($parsedCheckIn)) {
                    // No Show cancellation
                    if ($policyAdditionPercent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = $policyAdditionPercent->percent;
                    }
                }
                // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
                if ($parsedFreeDay->gt($dateNow)) {
                    // Free cancellation
                    if ($policy_percent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = $policy_percent->percent;
                    }
                }
            } else {
                if (!$policy_percent->is_first_night) {
                    $cancellationPayment = $policy_percent->percent;
                }
            }
            //$cancellationPayment = $policy_percent->percent;
            return [
                    'penalty' => $cancellationPayment,
                    'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
            ];
        } else {
            if ($policy_time->has_time) {
                $checkIn = $checkIn . ' ' . $policy_time->day . ':00';
                $parsedFreeDay = Carbon::parse($checkIn);
            } else {
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day + 1);
            }

            // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
            if ($dateNow->gt($parsedCheckIn)) {
                // No Show cancellation
                if ($policyAdditionPercent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = $policyAdditionPercent->percent;
                }
            }

            // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
            if ($parsedFreeDay->gt($dateNow)) {
                // Free cancellation
                $cancellationPayment = 0; 
                return $this->freeCancellation($checkOut);
            }

            // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах хугацааг хэтэрсэн мөн ирэх ёстой хугацаанаас өмнө байх тохиолдолд
            // тодорхой заасан төлбөрийг тооцож авна
            if ($parsedCheckIn->gt($parsedFreeDay) && $parsedCheckIn->gt($dateNow)) {
                // First Night cancellation
                if ($policy_percent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = $policy_percent->percent;
                }
            }

            return [
                'penalty' => $cancellationPayment,
                'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
            ];
        }
    }
    public function computePenaltyAmount($defaultPrice, $policy, $price, $usd, $checkIn, $checkOut, $numberOfUnits)
    {
        $cancellationPayment = 0;
        $deadline= false;
        $policy_percent = $policy->cancellationPercent()->first();
        $policyAdditionPercent = $policy->cancellationAdditionPercent()->first();
        $policy_time = $policy->cancellationTime()->first();
        $deadline = $policy_time->has_time ? false : $policy_time->day;
        $parsedCheckIn = Carbon::parse($checkIn);
 
        $nights = stayNights($checkIn, $checkOut, false);

        $penalties = [];
        // Төлбөр гарсан эсэхийг хугацаан дээрээс тооцоолж мэдэх
        // Хичнээн хэмжээний төлбөр гарсаныг цуцлалтын бодлогоос тооцох
        $dateNow = Carbon::now();
        // Reservation first nigth payment
        $firstNight = $price;
        $cancellationPayment = $firstNight;
        // array_push($penalties, [
        //             'penalty' => 0,
        //             'deadline'  => Carbon::parse($dateNow)->format('Y-m-d\TH:i:sP')
        // ]);
        if (!$policy->is_free) {
            if ($policy_time) {
               $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day);
                if ($policy_percent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = round($price / 100 * $policy_percent->percent) * $nights;
                }
                array_push($penalties, [
                    'penalty' => $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits,
                    'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
                ]);
                if ($policyAdditionPercent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = round($price / 100 * $policyAdditionPercent->percent) * $nights;
                }
                array_push($penalties, [
                    'penalty' => $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits,
                    'deadline'  => Carbon::parse($parsedCheckIn)->format('Y-m-d\TH:i:sP')
                ]);
            } else { // Цаг тохриуулагдсан байвал
               $parsedFreeDay = Carbon::parse($checkIn);
                 
                if (!$policy_percent->is_first_night) {
                    $cancellationPayment = round($price / 100 * $policy_percent->percent) * $nights;
                }

                array_push($penalties, [
                    'penalty' => $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits,
                    'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
                ]);
            }
            //$cancellationPayment = $policy_percent->percent;
            return $penalties;
        } else {
            if ($policy_time->has_time) {
                $checkIn = $checkIn . ' ' . $policy_time->day . ':00';
                $parsedFreeDay = Carbon::parse($checkIn);
            } else {
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day + 1);
            }

            if ($policy_percent->is_first_night) {
                $cancellationPayment = $firstNight;
            } else {
                $cancellationPayment = round($price / 100 * $policy_percent->percent) * $nights;
            }
            array_push($penalties, [
                'penalty' => $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits,
                'deadline'  => Carbon::parse($parsedFreeDay)->format('Y-m-d\TH:i:sP')
            ]);

             if ($policyAdditionPercent->is_first_night) {
                $cancellationPayment = $firstNight;
            } else {
                $cancellationPayment = round($price / 100 * $policyAdditionPercent->percent) * $nights;
            }
            array_push($penalties, [
                'penalty' => $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits,
                'deadline'  => Carbon::parse($parsedCheckIn)->format('Y-m-d\TH:i:sP')
            ]);
            return $penalties;
        }
    }
    public function computePenaltyAmountCancellation($defaultPrice, $policy, $price, $usd, $checkIn, $checkOut, $numberOfUnits)
    {
        $cancellationPayment = 0;
        $deadline= false;
        $policy_percent = $policy->cancellationPercent()->first();
        $policyAdditionPercent = $policy->cancellationAdditionPercent()->first();
        $policy_time = $policy->cancellationTime()->first();
        $deadline = $policy_time->has_time ? false : $policy_time->day;
        $parsedCheckIn = Carbon::parse($checkIn);

        $nights = stayNights($checkIn, $checkOut, false);

        // Төлбөр гарсан эсэхийг хугацаан дээрээс тооцоолж мэдэх
        // Хичнээн хэмжээний төлбөр гарсаныг цуцлалтын бодлогоос тооцох
        $dateNow = Carbon::now();
        // Reservation first nigth payment
        $firstNight = $price;
        $cancellationPayment = $firstNight;

        if (!$policy->is_free) {
            if ($policy_time) {
                // Цаг тохриуулагдсан байвал
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day);

                // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
                if ($dateNow->gt($parsedCheckIn)) {
                    // No Show cancellation
                    if ($policyAdditionPercent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = round($price / 100 * $policyAdditionPercent->percent) * $nights;
                    }
                }
                // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
                if ($parsedFreeDay->gt($dateNow)) {
                    // Free cancellation
                    if ($policy_percent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = round($price / 100 * $policy_percent->percent) * $nights;
                    }
                }
            } else {
                if (!$policy_percent->is_first_night) {
                    $cancellationPayment = round($price / 100 * $policy_percent->percent) * $nights;
                }
            }
            //$cancellationPayment = $policy_percent->percent;
            return $parsedFreeDay -> lt($dateNow) ? $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits : 0;
        } else {
            if ($policy_time->has_time) {
                $checkIn = explode(" ", $checkIn)[0] . ' ' . $policy_time->day . ':00';
                $parsedFreeDay = Carbon::parse($checkIn);
            } else {
                $parsedFreeDay = Carbon::parse($checkIn)->subDays($policy_time->day + 1);
            }

            // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
            if ($dateNow->gt($parsedCheckIn)) {
                // No Show cancellation
                if ($policyAdditionPercent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = round($price / 100 * $policyAdditionPercent->percent) * $nights;
                }
            }

            // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
            if ($parsedFreeDay->gt($dateNow)) {
                // Free cancellation
                $cancellationPayment = 0; 
                return $cancellationPayment;
            }

            // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах хугацааг хэтэрсэн мөн ирэх ёстой хугацаанаас өмнө байх тохиолдолд
            // тодорхой заасан төлбөрийг тооцож авна
            if ($parsedCheckIn->gt($parsedFreeDay) && $parsedCheckIn->gt($dateNow)) {
                // First Night cancellation
                if ($policy_percent->is_first_night) {
                    $cancellationPayment = $firstNight;
                } else {
                    $cancellationPayment = round($price / 100 * $policy_percent->percent) * $nights;
                }
            }

            return $parsedFreeDay -> lt($dateNow) ? $this->convertCurrency($cancellationPayment / $usd)* $numberOfUnits : 0;
        }
    }
    public function freeCancellation($checkOut) 
    {
        return [
            'penalty' => 99,
            'deadline'  => Carbon::parse($checkOut)->addDays(1)->format('Y-m-d\TH:i:sP')
        ];
    }

    public function mealType($meals)
    {
        $mealCode = 0;
        $lunch = 0;
        $dinner = 0;
        $breakfast = 0;
        foreach($meals as $m) {
            $mealArr[] = $m->code;
        }
        foreach($mealArr as $m) {
            if ($m == 'dinner') {
                $dinner = 1;
            } else if($m == 'lunch') {
                $lunch = 1;
            } else {
                $breakfast = 1;
            }
        }
        if (count($meals) > 2) {
            $mealCode = 7;
        } else if (count($meals) == 2) {
            if ($dinner && $lunch) {
                $mealCode = 3;
            } else if ($dinner && $breakfast) {
                $mealCode = 6;
            } else if($lunch && $breakfast) {
                $mealCode = 5;
            }
        } else if (count($meals) == 1) {
            if ($dinner) {
                $mealCode = 1;
            } else if($lunch) {
                $mealCode = 2;
            } else if($breakfast) {
                $mealCode = 4;
            }
        }

        return $mealCode;
    }

    public function fetchHotel($hotel) {
        // Check model has sync id
        if ($hotel->sync_id) {
            try {
                // Send request to ihotel
                $http = new \GuzzleHttp\Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/ctrip/hotel', [
                    'json' => [
                        'id' => $hotel->sync_id,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $data = json_decode($response->getBody());
                return $data->hotel;
            } catch (\Exception $e) {
                return $e;
            }
        } else {
            return null;
        }
    }

    public function fetchRoom($hotel) {
        // Check model has sync id
        if ($hotel->sync_id) {
            try {
                // Send request to ihotel
                $http = new \GuzzleHttp\Client;
                $response = $http->post(config('services.ihotel.baseUrl') . '/ctrip/hotel/rooms', [
                    'json' => [
                        'id' => $hotel->sync_id,
                    ],
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ]
                ]);

                $data = json_decode($response->getBody());
                return $data->roomTypes;
            } catch (\Exception $e) {
                return $e;
            }
        } else {
            return null;
        }
    }

    /**
     * Fetch currencies from capitron
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchCurrency($languageCode, $hotel) 
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->get(config('services.ihotel.baseUrl') . '/fetch/currency', [
            'connect_timeout' => 3,
        ]);

        // $rates = $hotel
        //     ->currencies()
        //     ->select(['id', 'name', 'short_name', 'rate', 'is_default'])
        //     ->get()
        //     ->toArray();

        $currCode = $languageCode === 'en-US' ? 'USD' : 'CNY';

        // // $rates = json_decode((string) $response->getBody(), true);

        // $filteredRates = array_filter($rates, function($item) use($currCode) {
        //     return strtoupper($item['short_name']) === $currCode && $item;
        // });

        // $rate = count($filteredRates) === 0 ? 1 : $filteredRates[key($filteredRates)]['rate'];
        $rate = json_decode((string) $response->getBody(), true);

        return $rate;
    }

    public function logResponse($response, $type = false, $logType = 'info') 
    {
        $title = ''; 
        if ($type) { // type = 1 hotel else room
            $title = 'Ctrip hotel notify response';
        } else {
            $title = 'Ctrip room notify response';
        }

        if ($logType === 'error') {
            Log::error($title, [
                'message' => $response->message,
                'code' => $response->code
            ]);
        } else {
            Log::info($title, [
                'message' => $response->message,
                'code' => $response->code,
                'requestId' => $response->requestId
            ]);
        }
    }
}
