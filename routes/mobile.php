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

$publicModules = [];

$modules = [
    'blocks' => 'BlockController',
    'groups' => 'GroupController',
];

$cloneModules = [
    'guest/clones' => 'GuestCloneController',
    // 'tax/clones' => 'TaxCloneController',
];

$reservationModules = [
    'items' => 'ItemController',
    'payments' => 'PaymentController',
];

// Extranet routes
Route::middleware('auth:api', 'verified')->group(function () {

    // Notifications
    Route::get('notifications', 'NotificationController@notifications');
    // Route::post('notifications/touch', 'NotificationController@touchNotification');
    // Fetch latest notifications
    Route::get('notifications/fetch', 'NotificationController@fetchNotifications');

    // Notify payment request to admin
    // Route::post('notify/payment', 'MobileController@notifyPaymentRequest');
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

    // Save new reservations
    Route::post('reservations/multiple', 'ReservationController@saveMultiple');
    /** Reservation routes */
    Route::get('reservations', 'ReservationController@index');
    Route::get('reservations/{id}', 'ReservationController@show');
    // Get reservations by date
    Route::get('reservations/by/dates', 'ReservationController@getByDates');
    // Get reservation details
    Route::get('reservations/g/{id}', 'ReservationController@getReservation');
    // Get reservation with service data
    Route::get('reservations/g/service/{id}', 'ReservationController@getService');

    // Reservation requests
    // Route::get('reservation/requests', 'ResReqController@index');
    // Route::get('reservation/requests/{id}', 'ResReqController@show');

    // Get new reservation data
    Route::get('reservations/g/new', 'ReservationController@getNewResData');
    // Calendar by dates
    Route::get('calendar/by/dates', 'CalendarController@calendarByDates');

    // Get available rooms by reservation
    Route::get('available/rooms/{id}', 'ReservationController@availableRooms');

    Route::middleware('res.available:same')->group(function () {
        // Update reservation
        Route::post('reservations', 'ReservationController@save')->name('updateReservation');
        // Assign room
        Route::post('assign/room', 'ReservationController@assignRoom')->name('assignRoom');
    });

    // Search available room types
    Route::post('room/types/search', 'RoomTypeController@search');

    // Rooms Map View
    Route::get('map/rooms', 'RoomController@getRoomsMapView');
    Route::post('searchRoomType', 'RoomTypeController@searchRoomType');

    // Report routes
    Route::get('report/payment/methods', 'ReportController@paymentMethodsReport');
    // Route::get('report/room/types', 'ReportController@roomTypesReport');
    Route::get('report/reservation', 'ReportController@reservationReport');
    // Route::get('report/reservations', 'ReservationController@reportReservations');
});

/*
|--------------------------------------------------------------------------
| MyHotel app API Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:api', 'hotel'])->group(function () {
    // Get statistics of dashboard
    Route::get('stats/res-reqs', 'MobileController@resRequestsStats');
});
