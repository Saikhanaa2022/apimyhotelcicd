<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth routes

$publicModules = [
    'amenity/categories' => 'AmenityCategoryController',
    'banks' => 'BankController',
    'bed/types' => 'BedTypeController',
    'countries' => 'CountryController',
    'districts' => 'DistrictController',
    'facility/categories' => 'FacilityCategoryController',
    'helps' => 'HelpController',
    'pages' => 'PageController',
    'hotel/types' => 'HotelTypeController',
    'product/categories' => 'ProductCategoryController',
    'provinces' => 'ProvinceController',
];

$modules = [
    'users' => 'UserController',
    'groups' => 'GroupController',
    'guests' => 'GuestController',
    'blocks' => 'BlockController',
    'sources' => 'SourceController',
    'currencies' => 'CurrencyController',
    'payment/methods' => 'PaymentMethodController',
    'partners' => 'PartnerController',
    'meals' => 'MealController',
    'rate/plans' => 'RatePlanController',
    'rate/plan/items' => 'RatePlanItemController',
    'intervals' => 'IntervalController',
    'room/types' => 'RoomTypeController',
    'rooms' => 'RoomController',
    'service/categories' => 'ServiceCategoryController',
    'services' => 'ServiceController',
    'taxes' => 'TaxController',
    'hotels' => 'HotelController',
    'hotel/banks' => 'HotelBankController',
    'hotel/rules' => 'HotelRuleController',
    'hotel/users' => 'HotelUsersController',
    'roles' => 'RoleController',
    'extrabed/policies' => 'ExtraBedPolicyController',
    'children/policies' => 'ChildrenPolicyController',
    'cancellation/policies' => 'CancellationPolicyController',
    'cancellation/times' => 'CancellationTimeController',
    'cancellation/percents' => 'CancellationPercentController',
    'invoices' => 'InvoiceController',
];

$cloneModules = [
    'guest/clones' => 'GuestCloneController',
    'tax/clones' => 'TaxCloneController',
];

$reservationModules = [
    'items' => 'ItemController',
    // 'charges' => 'ChargeController',
    'extra/beds' => 'ExtraBedController',
    'payments' => 'PaymentController',
];


Route::namespace('Auth')->group(function () {
    // Login
    Route::post('login', 'LoginController@login');
    // Admin login
    Route::post('admin/login', 'LoginController@loginAdmin');
    // Register
    Route::post('register', 'RegisterController@register');
    // Send token via email
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
    // Reset password
    Route::post('password/reset', 'ResetPasswordController@reset');
    // Verify email
    // Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
    // Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
    // Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');
});


// Public routes
// Route::get('states', 'AppController@showStates');
Route::get('property/check/tax', 'AppController@checkTaxByRegister');
// Get today
Route::get('today', 'AppController@today');

foreach ($publicModules as $route => $controller) {
    Route::get($route, $controller . '@index');
    Route::get($route . '/{id}', $controller . '@show');
}

// Authenticated user routes
Route::middleware(['auth:api'])->group(function () {
    // Return current user
    Route::get('user', 'AppController@currentUser');
    Route::post('user', 'AppController@updateCurrentUser');

    // Return permissions
    Route::get('permissions', 'PermissionController@index');
    // Route::get('roles/hotel/{id}', 'RoleController@getByHotel')->middleware('auth:api');

    // Send email
    Route::post('send/email', 'AppController@sendEmail');
});

// Extranet routes
Route::middleware('auth:api', 'verified')->group(function () {
    // Current user hotels
    Route::get('my/hotels', 'HotelController@getByUser');
    // Create new hotel
    Route::post('my/hotels', 'HotelController@save');
    // Update default user
    Route::post('users/owner', 'UserController@updateDefaultUser');
    // Change password
    Route::post('password/change', 'UserController@changePassword');
    // Upload image
    Route::post('image/upload', 'ImageController@upload');
    // Destroy image
    // Route::post('image/destroy', 'ImageController@destroy');
    // Notifications
    Route::get('notifications', 'NotificationController@notifications');
    Route::get('unread/notifications/count', 'NotificationController@unreadNotificationsCount');
    Route::post('notifications/touch', 'NotificationController@touchNotification');
    // Fetch latest notifications
    Route::get('notifications/fetch', 'NotificationController@fetchNotifications');
    // Send test notification
    Route::post('send/notification', 'UserController@sendNotification');
    // Notify payment request to admin
    Route::post('notify/payment', 'AppController@notifyPaymentRequest');
});
// Authenticated and hotel user routes
Route::middleware(['auth:api', 'hotel', 'permission'])->group(function () use ($modules, $cloneModules, $reservationModules) {
    foreach ($modules as $route => $controller) {
        Route::get($route, $controller . '@index');
        Route::get($route . '/{id}', $controller . '@show');
        Route::post($route, $controller . '@save');
        Route::post($route . '/delete', $controller . '@massDestroy');
        Route::delete($route . '/{id}', $controller . '@destroy');
    }

    foreach ($cloneModules as $route => $controller) {
        Route::post($route, $controller . '@save')->middleware('res.available');
        Route::delete($route . '/{id}', $controller . '@destroy')->middleware('res.available');
    }

    foreach ($reservationModules as $route => $controller) {
        Route::post($route, $controller . '@save')
            ->middleware('res.available');
        Route::delete($route . '/{id}', $controller . '@destroy')
            ->middleware('res.available');
    }

    /** Reservation routes */
    // Vat ArtLab connections
    Route::get('helps/list', 'HelpController@listWithPage');
    Route::get('reservations', 'ReservationController@index');
    Route::get('reservations/{id}', 'ReservationController@show');
    Route::post('vat', 'VatController@login');
    Route::get('vat/check', 'VatController@check');
    Route::post('vat/pos/bill', 'VatController@posBill');
    Route::post('vat/vatps/{id}', 'VatController@toVatps');
    Route::post('vat/product/list', 'VatController@productList');
    Route::post('vat/print', 'VatController@print');

    // Route::post('reservations/delete', 'ReservationController@massDestroy');
    // Route::delete('reservations/{id}', 'ReservationController@destroy');

    // Get reservation data
    Route::get('reservations/g/{id}', 'ReservationController@getReservation');
    Route::get('reservations/g/xroom/{id}', 'ReservationController@getXroomReservation');
    // Get new reservation data
    Route::get('reservations/g/new', 'ReservationController@getNewResData');
    // Get items by reservations
    Route::post('reservations/items', 'ReservationController@getItems');
    // Get reservations by date
    Route::get('reservations/by/dates', 'ReservationController@getByDates');

    // Check availability
    Route::post('check/reservation', 'ReservationController@checkAvailability');
    // Save new reservations
    Route::post('reservations/multiple', 'ReservationController@saveMultiple');

    Route::middleware('res.available:same')->group(function () {
        // Update reservation
        Route::post('reservations', 'ReservationController@save')->name('updateReservation');
        // Update reservation dates
        Route::post('reservation/dates', 'ReservationController@updateDates')->name('updateDates');
        // Update discount
        Route::post('reservation/discount', 'ReservationController@updateDiscount')->name('updateDiscount');
        // Update cancellation
        Route::post('reservation/cancellation', 'ReservationController@updateCancellation')->name('updateCancellation');
        // Assign room
        Route::post('assign/room', 'ReservationController@assignRoom')->name('assignRoom');
    });

    // Update reservations status
    // Route::post('reservations/status', 'ReservationController@updateMultiple');
    // Get available rooms by reservation
    Route::get('available/rooms/{id}', 'ReservationController@availableRooms');

    // Payment additional routes
    Route::get('incomes', 'PaymentController@index');
    Route::post('incomes/create', 'PaymentController@create');
    Route::post('incomes/update', 'PaymentController@update');

    // Hotel setting routes
    Route::get('hotel/setting', 'HotelSettingController@index');
    Route::post('hotel/setting', 'HotelSettingController@save');

    // Get hotel facilities
    Route::get('my/facilities', 'FacilityController@indexByHotel');

    // Count reports
    Route::get('report/count', 'AppController@countReport');
    // Route::post('register/hotel', 'RegisterController@registerHotel');

    // Check and update property tax information
    Route::get('property/update/tax', 'AppController@updateTaxByRegister');

    // Print payment bill
    Route::post('payments/bill/{id}', 'PaymentController@paymentBill');

    // Get blocks by date
    Route::get('blocks/by/dates', 'BlockController@getByDates');

    // Get rooms as resources
    Route::get('rooms/resources', 'RoomController@getRoomsAsResources');
    // Get rates by rate plan
    Route::get('rate/plans/{id}/rates', 'RatePlanController@getRates');
    // Save daily rates by rate plan
    Route::post('rate/plans/{id}/daily/rates', 'RatePlanController@saveDailyRates');
    // Update occupancy rate plan by rate plan
    Route::post('rate/plans/occupancy', 'RatePlanController@updateOccupancyRatePlan');
    // Update items of rate plan
    // Route::post('rate/plans/items', 'RatePlanController@updateItems');

    // Search available room types
    Route::post('room/types/search', 'RoomTypeController@search');
    // Check available room types
    Route::post('room/types/check', 'RoomTypeController@checkAvailability');
    // Upload room type images
    Route::post('room/types/{id}/images', 'RoomTypeController@saveImages');

    // Report routes
    Route::get('report/sales', 'ReportController@salesReport');
    Route::get('report/reservation', 'ReportController@reservationReport');

    // Route::get('report/payment/methods', 'ReportController@paymentMethodsReport');
    // Route::get('report/room/types', 'ReportController@roomTypesReport');
    // Route::get('report/channels', 'ReportController@sourcesReport');
    Route::get('report/reservations', 'ReservationController@reportReservations');

    // Update child age of hotel
    Route::post('hotel/update/age', 'HotelController@updateAge');
    Route::post('hotel/images', 'HotelController@saveImages');

    // Invoice email send
    Route::post('invoice/send/mail', 'InvoiceController@sendMail');

    // Get group reservation
    Route::get('group/reservation/{groupId}/{resId}', 'GroupController@getGroupReservation');
    // Get group payments
    Route::get('group/payments/{groupId}', 'GroupController@getGroupPayments');

    // Sync facilities to hotel
    Route::post('sync/facilities', 'HotelController@syncFacilities');

    // Sync amenities to room type
    Route::post('amenities/{id}', 'RoomTypeController@syncAmenities');

    /* Night audit */
    // Check night audit reservations
    Route::get('reservations/na-check', 'ReservationController@checkNightAudit');
    // Get night audit report data
    Route::get('reservations/na-report', 'ReservationController@getNightAuditReport');
    // Perform night audit
    Route::post('reservations/na-perform', 'ReservationController@performNightAudit');

    // Reservation requests
    Route::get('reservation/requests', 'ResReqController@index');
    Route::get('reservation/requests/{id}', 'ResReqController@show');

    Route::post('reservation/requests/check', 'ResReqController@checkAvailability');
    Route::post('reservation/requests/select/date', 'ResReqController@selectDate');

    // MyHotel App stats of dashboard
    Route::get('stats', 'ReportController@unreadNotificationsCount');

    // Get hotel edit data
    Route::get('hotels/data/edit', 'HotelController@getEditData');

    Route::post('update/roomtype', 'ReservationController@updateRoomtype');
});
// Check availability of hotel
// Route::post('check/availability', 'AppController@checkAvailability');

/*
|--------------------------------------------------------------------------
| Online book API Routes
|--------------------------------------------------------------------------
*/

Route::post('fetch/hotel', 'OnlineBookController@fetchHotel');
Route::post('fetch/room/types', 'OnlineBookController@fetchRoomTypes');
Route::post('store/reservation', 'OnlineBookController@storeReservation');

/*
|--------------------------------------------------------------------------
| iHotel API Routes
|--------------------------------------------------------------------------
*/
// Reservation
Route::post('create/reservation', 'IhotelController@createReservation');
Route::post('update/reservation', 'IhotelController@updateReservation');
Route::post('update/reservation/date', 'IhotelController@updateReservationDate');

// Reservation request
Route::post('create/res-req', 'IhotelController@createResRequest');
Route::post('update/res-req', 'IhotelController@updateResRequest');
Route::post('change/payment/res-req', 'IhotelController@changePaymentResRequest');
// Route::post('check/res-req', 'IhotelController@checkResRequest');
Route::post('select/date/res-req', 'IhotelController@selectDateResRequest');

/*
|--------------------------------------------------------------------------
| XRoom API Routes
|--------------------------------------------------------------------------
*/
// Reservation
Route::middleware('xroom')->group(function () {
    Route::get('xroom/init', 'XRoomReservationController@init');
    Route::post('xroom/invoice', 'XRoomReservationController@createInvoice');
    Route::post('xroom/check-code', 'XRoomReservationController@checkCode');
    Route::post('xroom/check-payment', 'XRoomReservationController@checkPaymentStatus');
    Route::get('xroom/hotel/search', 'XRoomV2Controller@searchHotels');
    Route::get('xroom/room-types', 'XRoomV2Controller@index');
    Route::get('xroom/hotel/locations', 'XRoomV2Controller@getCommonLocations');
    Route::get('xroom/room-type/{roomTypeId}/amenities', 'XRoomV2Controller@getAmenities');
    Route::get('xroom/history', 'XRoomV2Controller@getHistories');
    Route::post('xroom/history/{id}', 'XRoomV2Controller@deleteHistory');
    Route::get('xroom/bedtypes', 'XRoomV2Controller@getBedTypes');
    Route::get('xroom/hotels/nearby', 'XRoomV2Controller@nearby');

    // not really used this endpoints 
    Route::get('xroom/hotel/{id}', 'XRoomController@getHotel');
    Route::post('xroom/hotel/roomTypes', 'XRoomController@searchRoomTypes');
    Route::get('xroom/hotels/map', 'XRoomController@getHotelsMap');
    Route::get('xroom/cancel/reservation/{id}', 'XRoomController@cancelReservation');
    Route::get('xroom/reservations', 'XRoomController@getReservations');
    Route::get('xroom/transaction/{id}', 'XRoomController@createTransaction');
    Route::post('xroom/message', 'XRoomController@sendMessage');
});

Route::get('/xroom/qpay-payment-check', 'XRoomReservationController@qpayConfirm');
Route::get('/xroom/khanbank/test', 'XRoomBankController@getStatements');

/*
|--------------------------------------------------------------------------
| CTrip API Routes
|--------------------------------------------------------------------------
*/
// Authenticated and hotel user routes
Route::middleware(['auth:api', 'hotel', 'permission'])->group(function () {
    Route::get('ctrip/hotel', 'ChannelManagerController@connectHotel');
    Route::get('ctrip/hotel/rooms', 'ChannelManagerController@connectRoom');
});
// Test environment
Route::post('test/ctrip/hotel/availability/', 'ChannelManagerController@availabilityHotel');
Route::post('test/ctrip/room/availability/', 'ChannelManagerController@availabilityRoom');
Route::post('test/ctrip/booking', 'ChannelManagerController@booking');
Route::post('test/ctrip/cancellation', 'ChannelManagerController@cancellation');
// Live environment
Route::post('ctrip/hotel/availability', 'ChannelManagerController@availabilityHotel');
Route::post('ctrip/room/availability', 'ChannelManagerController@availabilityRoom');
Route::post('ctrip/booking', 'ChannelManagerController@booking');
Route::post('ctrip/cancellation', 'ChannelManagerController@cancellation');

/*
|--------------------------------------------------------------------------
| MyHotel app API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'hotel'])->group(function () {
    // Get statistics of dashboard
    Route::get('stats/res-reqs', 'AppController@resRequestsStats');
});