<?php

namespace App\Http\Controllers\Auth;

use App\Events\HotelCreated;
use App\Models\{User, Hotel, Source, PaymentMethod, Currency, CancellationPolicy, Role, Tax, RoomType, Room, RatePlan, OccupancyRatePlan, ServiceCategory, ProductCategory, Service};
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\{Hash, Validator};
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Validate with hotel data
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    // protected function validator(array $data)
    // {
    //     return Validator::make($data, [
    //         'registerNo' => 'required',
    //         'name' => 'required|string|max:255',
    //         'hotelTypeId' => 'required|integer',
    //         'position' => 'nullable|string|max:255',
    //         'phoneNumber' => 'required|integer',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8|same:passwordConfirmation|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[.#?!@$%^&*-]).{6,}$/',
    //         'hotelName' => 'required|string|max:255',
    //         'hotelCompanyName' => 'required|string|max:255',
    //     ], [
    //         'password.regex' => 'Нууц үг үсэг, тоо, тэмдэгт оруулсан байх шаардлагатай.'
    //     ]);
    // }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'phoneNumber' => 'required|integer',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|same:passwordConfirmation|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[.#?!@$%^&*-]).{6,}$/',
        ], [
            'password.regex' => 'Нууц үг үсэг, тоо, тэмдэгт оруулсан байх шаардлагатай.'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $params = array_merge($data, [
            'password' => Hash::make($data['password']),
            'isDefault' => true,
            'role_id',
        ]);

        return User::create(snakeCaseKeys($params));
    }

    public function register(Request $request)
    {
        $this->validator($request->all())
            ->validate();

        event(new Registered($user = $this->create($request->only([
            'name', 'position', 'phoneNumber', 'email', 'password',
        ]))));

        return response()->json([
            'user' => $user,
        ]);
    }

    // Register user with hotel data
    // public function register(Request $request)
    // {
    //     $this->validator($request->all())
    //         ->validate();

    //     event(new Registered($user = $this->create($request->only([
    //         'name', 'position', 'phoneNumber', 'email', 'password',
    //     ]))));

    //     $is_vatpayer = $request->input('isVatpayer');
    //     $is_citypayer = $request->input('isCitypayer');

    //     $hotel = Hotel::create([
    //         'name' => $request->input('hotelName'),
    //         'company_name' => $request->input('hotelCompanyName'),
    //         'register_no' => $request->input('registerNo'),
    //         'is_vatpayer' => $is_vatpayer,
    //         'is_citypayer' => $is_citypayer,
    //         'hotel_type_id' => $request->input('hotelTypeId'),
    //         'current_date' => date('Y-m-d'),
    //         'check_in_time' => '14:00',
    //         'check_out_time' => '12:00'
    //     ]);

    //     // Create default user role
    //     $role = Role::create([
    //         'name' => 'Админ',
    //         'is_default' => true,
    //     ]);
        
    //     $role->hotel()->associate($hotel);
    //     $role->save();

    //     // Syncing default user permission
    //     $permissionIds = \App\Models\Permission::select('id')->pluck('id');
    //     $role->permissions()->sync($permissionIds);

    //     // Create default source
    //     $source = Source::create([
    //         'name' => 'Ресепшн',
    //         'short_name' => 'РЕ',
    //         'color' => '#04639B',
    //         'hotel_id' => $hotel->id,
    //         'is_default' => true,
    //     ]);
        
    //     // Create default payment method
    //     $paymentMethod = PaymentMethod::create([
    //         'name' => 'Бэлэн мөнгө',
    //         'color' => '#04639B',
    //         'hotel_id' => $hotel->id,
    //         'is_default' => true,
    //         'is_paid' => true,
    //     ]);
        
    //     // Create default currency
    //     $currency = Currency::create([
    //         'name' => 'Төгрөг',
    //         'short_name' => 'MNT',
    //         'rate' => 1,
    //         'hotel_id' => $hotel->id,
    //         'is_default' => true,
    //     ]);

    //     // Create default taxes
    //     // Vat tax
    //     Tax::create([
    //         'name' => 'НӨАТ',
    //         'percentage' => 10,
    //         'inclusive' => true,
    //         'key' => 'vat',
    //         'is_default' => true,
    //         'is_enabled' => $is_vatpayer,
    //         'hotel_id' => $hotel->id,
    //     ]);
    //     // City tax
    //     Tax::create([
    //         'name' => 'НХАТ',
    //         'percentage' => 1,
    //         'inclusive' => true,
    //         'key' => 'city',
    //         'is_default' => true,
    //         'is_enabled' => $is_citypayer,
    //         'hotel_id' => $hotel->id,
    //     ]);
    //     // Service charge
    //     // Tax::create([
    //     //     'name' => 'Үйлчилгээний нэмэгдэл',
    //     //     'percentage' => 5,
    //     //     'inclusive' => false,
    //     //     'key' => null,
    //     //     'is_default' => false,
    //     //     'is_enabled' => true,
    //     //     'hotel_id' => $hotel->id,
    //     // ]);

    //     // Cancellation policy create
    //     CancellationPolicy::create([
    //         'is_free' => true,
    //         'has_prepayment' => false,
    //         'cancellation_time_id' => 3,
    //         'cancellation_percent_id' => 1,
    //         'addition_percent_id' => 1,
    //         'hotel_id' => $hotel->id,
    //     ]);

    //     // $hotel->users()->sync($user->id);
    //     $user->hotels()->sync($hotel->id);
    //     // $user->hotel()->associate($hotel);
    //     $user->role()->associate($role);
    //     $user->save();

    //     // Create sample room type
    //     $roomType = RoomType::create([
    //         'name' => 'Standard twin',
    //         'short_name' => 'ST',
    //         'occupancy' => 2,
    //         'hotel_id' => $hotel->id,
    //     ]);

    //     // Create sample rooms
    //     Room::insert([
    //         [
    //             'name' => '201',
    //             'status' => 'clean',
    //             'room_type_id' => $roomType->id
    //         ],
    //         [
    //             'name' => '202',
    //             'status' => 'clean',
    //             'room_type_id' => $roomType->id
    //         ]
    //     ]);

    //     // Create sample ratePlan
    //     $ratePlan = RatePlan::create([
    //         'name' => 'Үндсэн үнэ',
    //         'is_daily' => 1,
    //         'room_type_id' => $roomType->id
    //     ]);

    //     // Create sample occupancy rate plan
    //     OccupancyRatePlan::insert([
    //         [
    //             'occupancy' => 2,
    //             'discount_type' => 'currency',
    //             'discount' => 0,
    //             'is_active' => 0,
    //             'is_default' => 1,
    //             'rate_plan_id' => $ratePlan->id
    //         ],
    //         [
    //             'occupancy' => 1,
    //             'discount_type' => 'currency',
    //             'discount' => 0,
    //             'is_active' => 0,
    //             'is_default' => 0,
    //             'rate_plan_id' => $ratePlan->id
    //         ]
    //     ]);

    //     // Create sample service category
    //     $serviceCategory = ServiceCategory::create([
    //         'name' => 'Ресторан',
    //         'hotel_id' => $hotel->id
    //     ]);

    //     // Find product category
    //     $productCategory = ProductCategory::where('code', 2117600)->first();

    //     // Create sample service
    //     Service::create([
    //         'name' => 'Өглөөний цай',
    //         'price' => 9999,
    //         'countable' => 0,
    //         'product_category_id' => $productCategory->id,
    //         'service_category_id' => $serviceCategory->id
    //     ]);

    //     event(new HotelCreated($hotel));

    //     return response()->json([
    //         'user' => $user,
    //     ]);
    // }
}
