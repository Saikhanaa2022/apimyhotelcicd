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

$modules = [
    'amenity/categories' => 'AmenityCategoryController',
    'amenities' => 'AmenityController',
    'banks' => 'BankController',
    'bed/types' => 'BedTypeController',
    'channels' => 'ChannelController',
    'contacts' => 'ContactController',
    'countries' => 'CountryController',
    'districts' => 'DistrictController',
    'facility/categories' => 'FacilityCategoryController',
    'facilities' => 'FacilityController',
    'helps' => 'HelpController',
    'pages' => 'PageController',
    'hotel/types' => 'HotelTypeController',
    'hotel/banks' => 'HotelBankController',
    'hotels' => 'HotelController',
    'languages' => 'LanguageController',
    'product/categories' => 'ProductCategoryController',
    'provinces' => 'ProvinceController',
    // 'roles' => 'RoleController',
    'users' => 'UserController'
];

// Auth user routes
Route::prefix('admin')->middleware(['auth:api', 'hotel'])->group(function () use ($modules) {
    foreach ($modules as $route => $controller) {
        Route::get($route, $controller . '@indexByAdmin');
        Route::get($route . '/{id}', $controller . '@showByAdmin');
        Route::post($route, $controller . '@save');
        // ->middleware('model.exists');
        // Route::post($route . '/delete', $controller . '@massDestroy');
        Route::delete($route . '/{id}', $controller . '@destroyByAdmin');
    }

    // Count reports
    Route::get('report/count', 'AdminController@countReport');

    // Update user from admin
    Route::post('users/update', 'UserController@updateUser');

    // Change active state hotel
    Route::post('hotels/{id}/state', 'HotelController@changeState');

    // Sync hotel to wuBook
    Route::post('sync/hotel/wubook', 'HotelController@syncHotelWuBook');

    Route::post('fetch/data', 'HotelController@fetchData');
    // Sync hotel to ihotel.mn
    Route::post('sync/ihotel', 'HotelController@syncHotel');

    // Update hotel online book
    Route::post('update/onlinebook', 'HotelController@updateOnlineBook');
    // Update hotel online book
    Route::post('update/chatbot', 'HotelController@updateChatbot');
    // XRoom
    Route::get('xroom/room/types', 'HotelController@getXRoomRoomTypes');
    Route::post('sync/xroom', 'HotelController@syncXRoom');
    Route::get('xroom/configs', 'XRoomAdminController@getConfigs');
    Route::post('xroom/configs', 'XRoomAdminController@saveConfig');
    Route::get('xroom/reservations', 'XRoomAdminController@getXRoomReservations');
    Route::get('xroom/xroom-types', 'XRoomAdminController@getXRoomTypes');
    Route::post('xroom/xroom-types', 'XRoomAdminController@updateXRoomTypes');
    Route::post('xroom/xroom-types/order', 'XRoomAdminController@updateMultipleXRoomTypes');

    // CTrip
    // Route::post('ctrip/hotel', 'ChannelManagerController@hotelConnect');

    // Connect Any Source
    Route::post('connect/source', 'HotelController@connectSource');
    Route::post('source/room/types', 'HotelController@sourceRoomTypes');
    Route::post('connect/room/types', 'HotelController@connectRoomTypes');
});

// Route::prefix('admin')->group(function () use ($modules) {
//     // Fetch connection data from ihotel.mn
//     Route::post('fetch/data', 'HotelController@fetchData');
// });
