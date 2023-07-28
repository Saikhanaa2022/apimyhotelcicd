<?php

namespace App\Http\Controllers\Mobile;

use App\Models\{
    Contact, Group, Hotel, HotelBank, Reservation, ResReq, Service, ServiceCategory
};
use App\Events\{HotelActivated, ReservationEmailSend};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Hash, Validator};
use App\Jobs\SendEmail;
use App\Http\Controllers\Controller;

class MobileController extends Controller
{
    /**
     * Return authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentUser(Request $request)
    {
        $user = $request->user();

        // Load hotels
        $hotelsData = [];
        $hotels = $user->hotels()->get();

        foreach ($hotels as $hotel) {
            $hotelsData[] = [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'workingDate' => $hotel->working_date,
                'image' => $hotel->image,
                'isActive' => $hotel->is_active,
                'hasResRequest' => $hotel->hotelSetting->has_res_request,
            ];
        }

        $user->hotels = $hotelsData;

        $user->load('role.permissions');

        return response()->json([
            'user' => $user
        ]);
        // $user->load(['hotels' => function ($query) {
        //     $query->where('is_active', 1)
        //         // ->with(['hotelSetting' => function ($query) {
        //         //     $query->select(['hotel_id', 'has_night_audit']);
        //         // }])
        //         ->select(['hotels.id', 'hotels.name', 'hotels.working_date', 'hotels.image', 'hotels.is_active']);
        // }]);
    }

    /**
     * Update authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCurrentUser(Request $request)
    {
        // Validate
        Validator::make($request->all(), \App\Http\Requests\UserRequest::updateCurrentUserRules())
            ->validate();

        $params = $request->only([
            'name', 'position', 'phoneNumber',
        ]);

        $user = $request->user();

        if ($request->filled('password')) {
            $params = array_merge($params, [
                'password' => Hash::make($request->input('password')),
            ]);
        } else if ($request->filled('email') && $user->email != $request->input('email')) {
            $params = array_merge($params, [
                'email' => $request->input('email')
            ]);
        }

        $user->update(snakeCaseKeys($params));

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Return today.countReport
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function today()
    {
        $today = Carbon::today();
        // $hotel = $request->hotel;

        return response()->json([
            'date' => $today->format('Y-m-d'),
            // 'workingDate' => $hotel->working_date,
            // 'hasNightAudit' => $hotel->hotelSetting->hasNightAudit,
            // 'dayOfWeek' => dayOfWeek($today, 'mn'),
        ]);
    }

    /**
     * Return reservations counts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function countReport(Request $request)
    {
        $hotel = $request->hotel;
        $totalResRequests = $hotel->reservationRequests();

        return response()->json([
            'todayCheckin' => $this->todayCheckin($request),
            'todayCheckout' => $this->todayCheckout($request),
            'tomorrowCheckin' => $this->tomorrowCheckin($request),
            'todayStaying' => $this->todayStaying($request),
            'todayBooking' => $this->todayBooking($request),
            'todayIncome' => $this->todayIncome($request),
            'roomNights' => $this->roomNights($request),
            'hasNightAudit' => $hotel->hotelSetting->has_night_audit,
            'hasResRequest' => $hotel->hotelSetting->has_res_request,
            'reservationRequests' => [
                'total' => $totalResRequests
                    ->count(),
                'totalConfirmed' => $totalResRequests
                    ->whereIn('status', ['confirmed', 'reservation'])
                    ->count(),
                'totalAmount' => $totalResRequests
                    ->whereIn('status', ['confirmed', 'reservation'])
                    ->sum('amount'),
                'paidAmount' => $totalResRequests
                    ->whereIn('status', ['confirmed', 'reservation'])
                    ->where('is_paid', true)
                    ->sum('amount_paid'),
                ]
        ]);
    }
    
    /**
     * Өнөөдөр ирэх захиалгын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function todayCheckin(Request $request)
    {
        return $request->hotel
            ->reservations()
            ->whereDate('check_in', $request->hotel->working_date)
            ->whereIn('status', [
                'pending', 'confirmed', 'no-show',
            ])
            ->count();
    }

    /**
     * Өнөөдөр гарах захиалгын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function todayCheckout(Request $request)
    {
        return $request->hotel
            ->reservations()
            ->whereDate('check_out', $request->hotel->working_date)
            ->where('status', 'checked-in')
            ->count();
    }

    /**
     * Өнөөдөр гарах захиалгын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tomorrowCheckin(Request $request)
    {
        $tomorrow = Carbon::parse($request->hotel->working_date)->addDay(1);

        return $request->hotel
            ->reservations()
            ->whereDate('check_in', $tomorrow)
            ->whereIn('status', [
                'pending', 'confirmed', 'no-show',
            ])
            ->count();
    }

    /**
     * Өнөөдөр байрлаж байгаа захиалгын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function todayStaying(Request $request)
    {
        // User's hotel
        $hotel = $request->hotel;
        
        // Current date
        $currentDate = $hotel->working_date;

        $reservations = $hotel->reservations()
            ->whereDate('check_in', '<=', $currentDate)
            ->whereDate('check_out', '>=', $currentDate)
            ->where('status', 'checked-in')
            ->get();

        $count = count($reservations);

        $personCount = 0;
        $childCount = 0;

        foreach($reservations as $r) {
            $personCount += $r->number_of_guests;
            $childCount += $r->number_of_children;
        }

        $occupancy = 0;
        $totalRooms = $hotel->rooms()
            ->availableIn($currentDate, $currentDate)
            ->count();
        
        if ($totalRooms) {
            $occupancy = intval($count / $totalRooms * 100);
        }

        return [
            'count' => $count,
            'occupancy' => $occupancy,
            'personCount' => $personCount,
            'childCount' => $childCount
        ];
    }

    /**
     * Өнөөдөр үүссэн захиалгын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function todayBooking(Request $request)
    {
        return $request->hotel
            ->reservations()
            ->whereDate('posted_date', $request->hotel->working_date)
            ->count();
    }

    /**
     * Өнөөдөр орлогын дүн // Payments дээр систем огноо тооцох
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function todayIncome(Request $request)
    {
        $workingDate = $request->hotel->working_date;
        $todayIncome = $request->hotel->payments
            // ->where('is_active', 1)
            ->where('income_type', '!=', 'receivable')
            ->where('posted_date', $workingDate)
            // ->map(function ($item, $key) use ($workingDate) {
            //     // Payment pay's total amount 
            //     $item->total = $item->pays()
            //         // ->whereDate('payment_pays.created_at', $workingDate)
            //         ->sum('amount');

            //     return $item;
            // })
            ->sum('amount');

        // Get paid income today receivable, policy ...
        // $todayIncome += $todayIncome;

        return $todayIncome;
    }

    /**
     * Нийт ор/хоногийн тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function roomNights(Request $request)
    {
        // User's hotel
        $hotel = $request->hotel;

        // Current date
        $currentDate = $hotel->working_date;

        // Occupied
        $occupiedReservations = $hotel->reservations()
            ->whereDate('check_in', '<=', $currentDate)
            ->whereDate('check_out', '>=', $currentDate)
            ->where('status', 'checked-in')->get();
            
        // Room Nights = rooms blocked or occupied multiplied by the number of nights each room is reserved or occupied.
        return $occupiedReservations->sum('stay_nights');
    }

    /**
     * Return stats of reservation requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resRequestsStats(Request $request)
    {
        $hotel = $request->hotel;
        // Get all res requests
        $totalResRequests = $hotel->reservationRequests();
        // Get today res requests
        // $todayResRequests = $hotel->reservationRequests()
        //     ->whereDate('created_at', Carbon::today());

        return response()->json([
            'hasResRequest' => $hotel->hotelSetting->has_res_request,
            'total' => $totalResRequests
                ->count(),
            'totalConfirmed' => $totalResRequests
                ->whereIn('status', ['confirmed', 'reservation'])
                ->count(),
            'totalAmount' => $totalResRequests
                ->whereIn('status', ['confirmed', 'reservation'])
                ->sum('amount'),
            'paidAmount' => $totalResRequests
                ->whereIn('status', ['confirmed', 'reservation'])
                ->where('is_paid', true)
                ->sum('amount_paid'),
            // 'today' => [
            //     'total' => $todayResRequests
            //         ->count(),
            //     'confirmed' => $todayResRequests
            //         ->where('status', 'confirmed')
            //         ->count(),
            //     'totalAmount' => $todayResRequests->sum('amount'),
            //     'paidAmount' => $todayResRequests
            //         ->where('is_paid', true)
            //         ->sum('amount_paid'),
            // ],
            // 'all' => [
            // ]
        ]);
    }

    /**
     * Notify payment request to admin
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function notifyPaymentRequest(Request $request) {
        $request->validate([
            'id' => 'required|integer',
            'accountId' => 'nullable|integer',
            'objectType' => 'required|string|in:reservation,resReq'
        ]);

        $id = $request->input('id');
        $accountId = $request->input('accountId');
        $objectType = $request->input('objectType');

        if ($objectType == 'resReq')
            $object = ResReq::find($id);
        else if ($objectType == 'reservation')
            $object = Reservation::find($id);
        else
            $object = NULL;

        if (!is_null($object)) {
            // Find hotel find account
            $account = NULL;
            if ($request->filled('accountId')) {
                $account = HotelBank::find($accountId);
                $account = [
                    'bank' => $account->bank->name,
                    'name' => $account->account_name,
                    'number' => $account->number,
                    'currency' => $account->currency
                ];
            }

            $emailData = [
                'toEmail' => 'sales@ihotel.mn',
                'bccEmails' => ['enkhtogtokh@ihotel.mn'],
                'emailType' => 'notifyPayment',
                'objectType' => $objectType,
                'account' => $account
            ];

            // Check request is already sent
            if (!$object->is_fetch_payment) {
                // Trigger event
                SendEmail::dispatch($emailData, $object);
            }

            $object->is_fetch_payment = true;
            $object->update();

            return response()->json([
                'success' => true,
                'message' => 'Амжилттай илгээгдлээ.',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Үйлдэл гүйцэтгэхэд алдаа гарлаа.',
        ], 200);
    }

    /**
     * Return states.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showStates()
    {
        $states = [
            "Afghanistan",
            "Åland Islands",
            "Albania",
            "Algeria",
            "American Samoa",
            "Andorra",
            "Angola",
            "Anguilla",
            "Antarctica",
            "Antigua and Barbuda",
            "Argentina",
            "Armenia",
            "Aruba",
            "Australia",
            "Austria",
            "Azerbaijan",
            "Bahamas",
            "Bahrain",
            "Bangladesh",
            "Barbados",
            "Belarus",
            "Belgium",
            "Belize",
            "Benin",
            "Bermuda",
            "Bhutan",
            "Bolivia (Plurinational State of)",
            "Bonaire, Sint Eustatius and Saba",
            "Bosnia and Herzegovina",
            "Botswana",
            "Bouvet Island",
            "Brazil",
            "British Indian Ocean Territory",
            "United States Minor Outlying Islands",
            "Virgin Islands (British)",
            "Virgin Islands (U.S.)",
            "Brunei Darussalam",
            "Bulgaria",
            "Burkina Faso",
            "Burundi",
            "Cambodia",
            "Cameroon",
            "Canada",
            "Cabo Verde",
            "Cayman Islands",
            "Central African Republic",
            "Chad",
            "Chile",
            "China",
            "Christmas Island",
            "Cocos (Keeling) Islands",
            "Colombia",
            "Comoros",
            "Congo",
            "Congo (Democratic Republic of the)",
            "Cook Islands",
            "Costa Rica",
            "Croatia",
            "Cuba",
            "Curaçao",
            "Cyprus",
            "Czech Republic",
            "Denmark",
            "Djibouti",
            "Dominica",
            "Dominican Republic",
            "Ecuador",
            "Egypt",
            "El Salvador",
            "Equatorial Guinea",
            "Eritrea",
            "Estonia",
            "Ethiopia",
            "Falkland Islands (Malvinas)",
            "Faroe Islands",
            "Fiji",
            "Finland",
            "France",
            "French Guiana",
            "French Polynesia",
            "French Southern Territories",
            "Gabon",
            "Gambia",
            "Georgia",
            "Germany",
            "Ghana",
            "Gibraltar",
            "Greece",
            "Greenland",
            "Grenada",
            "Guadeloupe",
            "Guam",
            "Guatemala",
            "Guernsey",
            "Guinea",
            "Guinea-Bissau",
            "Guyana",
            "Haiti",
            "Heard Island and McDonald Islands",
            "Holy See",
            "Honduras",
            "Hong Kong",
            "Hungary",
            "Iceland",
            "India",
            "Indonesia",
            "Côte d'Ivoire",
            "Iran (Islamic Republic of)",
            "Iraq",
            "Ireland",
            "Isle of Man",
            "Israel",
            "Italy",
            "Jamaica",
            "Japan",
            "Jersey",
            "Jordan",
            "Kazakhstan",
            "Kenya",
            "Kiribati",
            "Kuwait",
            "Kyrgyzstan",
            "Lao People's Democratic Republic",
            "Latvia",
            "Lebanon",
            "Lesotho",
            "Liberia",
            "Libya",
            "Liechtenstein",
            "Lithuania",
            "Luxembourg",
            "Macao",
            "Macedonia (the former Yugoslav Republic of)",
            "Madagascar",
            "Malawi",
            "Malaysia",
            "Maldives",
            "Mali",
            "Malta",
            "Marshall Islands",
            "Martinique",
            "Mauritania",
            "Mauritius",
            "Mayotte",
            "Mexico",
            "Micronesia (Federated States of)",
            "Moldova (Republic of)",
            "Monaco",
            "Mongolia",
            "Montenegro",
            "Montserrat",
            "Morocco",
            "Mozambique",
            "Myanmar",
            "Namibia",
            "Nauru",
            "Nepal",
            "Netherlands",
            "New Caledonia",
            "New Zealand",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Niue",
            "Norfolk Island",
            "Korea (Democratic People's Republic of)",
            "Northern Mariana Islands",
            "Norway",
            "Oman",
            "Pakistan",
            "Palau",
            "Palestine, State of",
            "Panama",
            "Papua New Guinea",
            "Paraguay",
            "Peru",
            "Philippines",
            "Pitcairn",
            "Poland",
            "Portugal",
            "Puerto Rico",
            "Qatar",
            "Republic of Kosovo",
            "Réunion",
            "Romania",
            "Russian Federation",
            "Rwanda",
            "Saint Barthélemy",
            "Saint Helena, Ascension and Tristan da Cunha",
            "Saint Kitts and Nevis",
            "Saint Lucia",
            "Saint Martin (French part)",
            "Saint Pierre and Miquelon",
            "Saint Vincent and the Grenadines",
            "Samoa",
            "San Marino",
            "Sao Tome and Principe",
            "Saudi Arabia",
            "Senegal",
            "Serbia",
            "Seychelles",
            "Sierra Leone",
            "Singapore",
            "Sint Maarten (Dutch part)",
            "Slovakia",
            "Slovenia",
            "Solomon Islands",
            "Somalia",
            "South Africa",
            "South Georgia and the South Sandwich Islands",
            "Korea (Republic of)",
            "South Sudan",
            "Spain",
            "Sri Lanka",
            "Sudan",
            "Suriname",
            "Svalbard and Jan Mayen",
            "Swaziland",
            "Sweden",
            "Switzerland",
            "Syrian Arab Republic",
            "Taiwan",
            "Tajikistan",
            "Tanzania, United Republic of",
            "Thailand",
            "Timor-Leste",
            "Togo",
            "Tokelau",
            "Tonga",
            "Trinidad and Tobago",
            "Tunisia",
            "Turkey",
            "Turkmenistan",
            "Turks and Caicos Islands",
            "Tuvalu",
            "Uganda",
            "Ukraine",
            "United Arab Emirates",
            "United Kingdom of Great Britain and Northern Ireland",
            "United States of America",
            "Uruguay",
            "Uzbekistan",
            "Vanuatu",
            "Venezuela (Bolivarian Republic of)",
            "Viet Nam",
            "Wallis and Futuna",
            "Western Sahara",
            "Yemen",
            "Zambia",
            "Zimbabwe",
        ];

        return response()->json([
            'states' => $states,
        ]);
    }

    /**
     * Check property tax info from ebarimt.mn function.
     *
     */
    public function checkTaxByRegister(Request $request) 
    {
        $request->validate([
            'register_no' => 'required' . ($request->filled('check') ? '' : '|unique:hotels'),
        ]);
        if($request->has('register_no')) {
            $http = new \GuzzleHttp\Client;

            $response = $http->get(url('https://ihotel.mn/api/check/property?register_no='.$request->input('register_no')));
            $isFound = json_decode($response->getBody())->found;
            
            if(!$isFound) {
                return response()->json([
                    'message' => 'Аж ахуй нэгж бүртгэлгүй байна.',
                ], 400);
            }

            return response()->json(json_decode((string) $response->getBody(), true));
        }
        
        return response()->json([
            'message' => 'Алдаа гарлаа. Аж ахуй нэгжийн регистрийн дугаараа оруулна уу.',
        ], 400);
    }

    /**
     * Check property tax info from ebarimt.mn then update function.
     *
     */
    public function updateTaxByRegister(Request $request) {
        // Get hotel from requested user
        $hotel = $request->hotel;
        if(!is_null($hotel)) {
            $http = new \GuzzleHttp\Client;

            $response = $http->get(url('https://ihotel.mn/api/check/property?register_no='.$hotel->register_no));
            $data = json_decode($response->getBody());
            
            if ($data->found) {
                $hotel->is_vatpayer = $data->vatpayer;
                $hotel->is_citypayer = $data->citypayer;
                $hotel->update();

                foreach($hotel->taxes as $tax) {
                    // НӨАТ
                    if ($tax->key === 'vat') {
                        $tax->is_enabled = $hotel->is_vatpayer; 
                    }
                    // НХАТ
                    if ($tax->key === 'city') {
                        $tax->is_enabled = $hotel->is_citypayer;
                    }
                    $tax->save();
                }
            }

            return response()->json([
                'message' => 'success',
            ], 200);
        }

        return response()->json([
            'message' => 'Алдаа гарлаа.',
        ], 400);
    }

    /**
     * Send email for reservation documents.
     *
     */
    public function sendEmail(Request $request) 
    {

        // Validate request
        $request->validate([
            'id' => 'required|integer',
            'isGroup' => 'required|boolean',
            'email' => 'required|email|max:255',
            'type' => 'required|string'
        ]);

        $data = null;

        // Check is group
        if ($request->isGroup) {
            // Find group
            $data = Group::find($request->id);
        } else {
            // Find reservation
            $data = Reservation::find($request->id);
        }

        // Check data
        if (is_null($data)) {
            return response()->json([
                'status' => false,
                'message' => 'Resouce not found.'
            ], 400);
        }

        $dataId = $request->input('id');

        // Fire event that related to reservation 
        event(new ReservationEmailSend($dataId, $request->email, $request->type, $request->isGroup));

        return response()->json([
            'message' => 'success',
        ], 200);
    }

    /**
     * Update the status of hotel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id 
     */
    public function changeState(Request $request, $id) 
    {
        if ($request->has('from') && $request->get('from') === 'mail') {
            // Find hotel
            $hotel = Hotel::find($id);
            // Get old value
            $sendEmail = $hotel->is_active;
            $hotel->is_active = true;
            $hotel->working_date = Carbon::today();
            $hotel->update();

            if (!$sendEmail) {
                event(new HotelActivated($hotel, true));
                dd("Aмжилттай идэвхжүүллээ.");
            }

            dd("Аль хэдийн идэвхжүүлсэн байна.");
        }
        return redirect(config('services.dashboard.loginUrl'));
    }

    /**
     * Sync service add. Not used function
     *
     */
    public function syncServiceAdd() 
    {

        $hotels = Hotel::all();

        $services = [
            'Зурагт',
            'Минибар',
            'Ус буцалгагч',
            'Хаалга',
            'Электрон хаалганы цоож',
            'Энгийн хаалганы цоож',
            'Гэрлийн бүрхүүл (Гэрэл)',
            'Орны гэрэл',
            'Шилэн аяга',
            'Кофены аяга',
            'Шилэн стакан',
            'Хөшиг тогтоогч',
            'Орны шүүгээ',
            'Дэвсгэр даавуу',
            'Хөнжлийн углаа',
            'Дэрний уут',
            'Нүүрний алчуур',
            'Биеийн алчуур',
            'Халад',
            'Хөшиг',
            'Тюль',
            'Дэр',
            'Хөнжил',
            'Гудас',
            'Цонх',
            'Ширээ',
            'Сандал',
            'Үсний сэнс',
            '00-н толь',
            '00-н хаалганы цоож',
            '00-н хаалга',
            '00-н суултуур',
            '00-н угаалтуур',
            '00-н шилэн стакан',
        ];

        /*
         */

        // Add all hotels service category 
        foreach($hotels as $h) {
            $service_category = ServiceCategory::create([
                'name' => 'Эвдрэл гэмтэл',
                'is_default' => true,
                'hotel_id' => $h->id
            ]);
            // Add services
            foreach($services as $s) {
                Service::create([
                    'name' => $s,
                    'price' => 10000,
                    'countable' => 0,
                    'service_category_id' => $service_category->id
                ]);
            }
        }
        
        dd('done');
    }

    /**
     * Сул өрөөтэй өрөөний төрлүүд хайх.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function checkAvailability(Request $request)
    // {
    //     // Validation
    //     $request->validate([
    //         'checkIn' => 'required|date_format:Y-m-d',
    //         'checkOut' => 'required|date_format:Y-m-d',
    //         'hotelId' => 'required|integer',
    //     ]);

    //     // Get input params
    //     $checkIn = $request->input('checkIn') . ' 14:00';
    //     $checkOut = $request->input('checkOut') . ' 12:00';
    //     $hotelId = $request->input('hotelId');

    //     // Find hotel
    //     $hotel = Hotel::find($hotelId);

    //     if ($hotel) {
    //         // Check room types
    //         $roomTypes = $hotel->roomTypes()
    //             ->select(['id', 'name'])
    //             ->withCount(['rooms' => function ($query) use ($checkIn, $checkOut) {
    //                 $query->unassigned($checkIn, $checkOut);
    //             }])
    //             ->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => '',
    //             'roomTypes' => $roomTypes,
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Record not found.',
    //         ], 400);
    //     }
    // }

    /**
     * Test function.
     *
     */
    public function dracula($key) 
    {
        if ($key === 'fireOnesignal') {
            $user = \App\Models\User::find(1);
            $user->notify(new \App\Notifications\TestOneSignal());
            dd('fireOnesignal');
        }

        if ($key === 'checkEmailSending') {
            $data = [
                'toEmail' => 'dev@ihotel.mn',
                'bccEmails' => ['info@ihotel.mn']
            ];

            // Find user by email
            $user = \App\Models\User::whereEmail($data['toEmail'])->first();

            // Notify to user
            if (!is_null($user)) {
                $user->notify(new \App\Notifications\TestNotification($data));
            }

            dd('checkEmailSending');
        }
        
        abort(404);

        // if ($key === 'fixStayTypes') {
        //     $reservations = Reservation::where('is_time', true)->get();

        //     foreach ($reservations as $item) {
        //         $hotel = $item->hotel;
        //         $checkIn = $item->check_in;
        //         $checkOut = $item->check_out;

        //         $checkInTime = Carbon::parse($checkIn)->format('H:i');
        //         $checkOutTime = Carbon::parse($checkOut)->format('H:i');

        //         if ($checkInTime == '14:00' && $checkOutTime == '18:00') {
        //             $item->stay_type = 'day';
        //         } else {
        //             if ($hotel->has_time) {
        //                 $diffHours = Carbon::parse($item->check_out)->diffInHours(Carbon::parse($item->check_in));
        //                 if ($diffHours > $hotel->max_time) {
        //                     $item->stay_type = 'day';
        //                 } else {
        //                     $item->stay_type = 'time';
        //                 }
        //             } else {
        //                 $item->stay_type = 'day';
        //             }
        //         }

        //         $item->save();
        //     }

        //     dd('Done: ' . count($reservations));
        // }
        // if ($key === 'paidAmount!@@!') {
        //     $this->fixPaidAmount();
        // } else if ($key === 'payments!@@!') {
        //     $this->fixPayments();
        // } else if ($key === 'hotelSettings!@@!') {
        //     $this->fixHotelSettings();
        // } else if ($key === 'workingDate!@@!') {
        //     $this->fixWorkingDate();
        // } else if ($key === 'paymentMethods!@@!') {
        //     $this->fixPaymentMethods();
    }

    /**
     * Fix system date hotels and reservations function.
     *
     */
    public function fixWorkingDate() 
    {
        $hotels = Hotel::all();
        $count = 0;
        foreach ($hotels as $hotel) {
            $hotel->working_date = Carbon::today();
            $hotel->update();

            foreach ($hotel->reservations as $res) {
                $res->posted_date = Carbon::parse($res->created_at)->format('Y-m-d');
                $res->update();
            }
            $count++;
        }
        dd($count);
    }

    /**
     * Fix system date of payments function.
     *
     */
    // public function fixPayments() {
    //     $hotels = Hotel::all();
    //     foreach ($hotels as $hotel) {
    //         foreach ($hotel->payments as $payment) {
    //             // Get reservation and guest clone, created_at
    //             $res = $payment->reservation;
    //             $guestClone = $res->guestClone;
    //             $paymentCreatedAt = Carbon::parse($payment->created_at)->format('Y-m-d');

    //             // Generate pays
    //             $pays = [];

    //             foreach ($payment->pays as $pay) {
    //                 array_push($pays, [
    //                     'id' => $pay->id,
    //                     'amount' => $pay->amount,
    //                     'payment_method' => $pay->paymentMethodClone->name,
    //                     'payment_method_clone_id' => $pay->payment_method_clone_id
    //                 ]);
    //             }

    //             if (is_null($payment->posted_date)) {
    //                 $payment->posted_date = $paymentCreatedAt;
    //             }
    //             $payment->income_pays = json_encode($pays, JSON_UNESCAPED_UNICODE);
    //             $payment->payer = json_encode([
    //                     'name' => $guestClone->name,
    //                     'surname' => $guestClone->surname,
    //                     'phone_number' => $guestClone->phone_number,
    //                     'email' => $guestClone->email,
    //                     'passport_number' => $guestClone->passport_number,
    //                 ], JSON_UNESCAPED_UNICODE);
    //             $payment->paid_at = $paymentCreatedAt;

    //             $payment->update();
    //         }
    //     }

    //     dd('Done.');
    // }

    /**
     * Fix paid amount reservations function.
     *
     */
    // public function fixPaidAmount() {
    //     $hotels = Hotel::all();
    //     $count = 0;
    //     foreach ($hotels as $hotel) {
    //         foreach ($hotel->reservations as $res) {
    //             if ($res->amount_paid === 0) {
    //                 $res->amount_paid = $res->payments()
    //                     ->where('is_active', 1)
    //                     ->sum('amount');
    //                 $res->update();
    //                 $count++;
    //             }
    //         }
    //     }

    //     dd($count);
    // }

    /** NOT DONE YET FIX HERE
     * Fix paid amount reservations function.
     *
     */
    public function fixReservationsStatus() 
    {
        $hotels = Hotel::all();
        $count = 0;

        // foreach ($hotels as $hotel) {
        //     $reservations = $hotel->reservations()
        //         ->where('is_audited', 0)
        //         ->get();

        //     foreach ($reservations as $res) {
        //         $status = $res->status;
        //         dd($res);
        //         if ($status === 'pending') {
                    
        //         } else if ($status === 'confirmed') {
 
        //         } else if ($status === 'canceled') {
 
        //         } else if ($status === 'checked-in') {
 
        //         } else if ($status === 'checked-out') {
 
        //         } else if ($status === 'no-show') {
                    
        //         }

        //     }
        // }

        dd('Done.');
    }

    /**
     * Fix hotel settings of hotels function.
     *
     */
    // public function fixHotelSettings() {
    //     $hotelSettings = \App\Models\HotelSetting::all();

    //     foreach ($hotelSettings as $hs) {
    //         $hs->is_nightaudit_auto = true;
    //         $hs->update();
    //     }

    //     dd('Done.');
    // }

    /**
     * Fix payment methods function.
     *
     */
    // public function fixPaymentMethods() {
    //     $hotels = Hotel::all();
    //     foreach ($hotels as $hotel) {
    //         foreach ($hotel->paymentMethods as $pm) {
    //             $pm->income_types = '["paid"]';
    //             $pm->update();
    //         }
    //     }

    //     dd('Done');
    // }
}
