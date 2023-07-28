<?php

use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth.very_basic'], function() {
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
});

// Return image
Route::get('image', 'ImageController@render');
Route::get('qr/image', function (Illuminate\Http\Request $request) {
    // return \QrCode::format('png')->size(500)->generate($request->query('code'));
    return QrCode::size(500)->generate($request->query('code'));
});

// Change hotel is active from email
Route::get('hotels/state/{id}', 'AppController@changeState');

// Add service category 'Эвдрэл гэмтэл'
Route::get('add/service/category', 'AppController@syncServiceAdd');

// Test function
Route::get('dracula/{key}', 'AppController@dracula');

// Export excel file
// Route::get('download/excel', 'ReservationController@exportExcelFile');

Route::middleware(['xroom'])->group(function () {
    // QPay Generate Invoice
    Route::post('qpay/generate/invoice', 'QpayController@generateInvoice');
    Route::post('qpay/check/payment', 'QpayController@checkPayment');
    Route::get('qpay/payment', 'QpayController@qpayWebhook');
    Route::post('qpay/payment', 'QpayController@qpayWebhook');
    Route::get('reservation/payment/{id}', 'XRoomController@showReservationPayment')->middleware('order');
    Route::post('reservation/payment/check/{id}', 'XRoomController@checkReservationPayment')->middleware('order');

    // Mongolchat Route
    Route::post('mc/generate/qr', 'MongolChatController@generateQr');
    Route::post('mc/check/payment', 'MongolChatController@qrStatus');
    Route::post('mc/settle', 'MongolChatController@settle');
    Route::post('mc/refund', 'MongolChatController@refund');
    Route::post('mc/payment', 'MongolChatController@mcWebhook');
    Route::get('reservation/payment/mc/{id}', 'XRoomController@showReservationPaymentMC')->middleware('order');
    Route::post('reservation/payment/check/mc/{id}', 'XRoomController@checkReservationPaymentMc')->middleware('order');

    // Lend Route
    Route::post('lend/generate/invoice', 'LendController@generateInvoice');
    Route::post('lend/check/payment', 'LendController@checkPayment');
    Route::post('lend/cancel/invoice', 'LendController@cancelInvoice');
    Route::post('lend/payment', 'LendController@webHook');
    Route::post('reservation/payment/check/lend/{id}', 'XRoomController@checkReservationPaymentLend')->middleware('order');
    Route::get('reservation/payment/lend/{id}', 'XRoomController@showReservationPaymentLend')->middleware('order');

    // Golomt Route
    // Route::post('g/push', 'GolomtPayment@pushPayment');
    // Route::post('g/paybill', 'GolomtPayment@paybill');
});
