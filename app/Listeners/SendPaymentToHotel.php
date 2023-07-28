<?php

namespace App\Listeners;

use App\Models\Hotel;
use App\Models\HotelBank;
use App\Models\XRoomTransfer;
use App\Services\KhanbankService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendPaymentToHotel
{

    private $khanbankService;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(KhanbankService $khanbankService)
    {
        //
        $this->khanbankService = $khanbankService;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // TODO: myhotel дээр захиалга үүсгэсний дараа орж ирсэн төлбөрийг хуваарилна
        $reservation = $event->reservation;

        Log::info('starting transfer money to hotel: ' . $event->reservation->id);

        $hotel_id = $reservation->hotel_id;
        $hotel_bank = HotelBank::with('bank')->where('hotel_id', $hotel_id)->where('is_default', 1)->first();

        $transfer = new XRoomTransfer();
        $transfer->hotel_id = $hotel_id;
        $transfer->room_type_id = $reservation->room_type_id;
        $transfer->amount = $reservation->amount;

        if ($hotel_bank == null) {
            $transfer->code = '101';
            $transfer->message = 'bank not created';
            $transfer->status = 'error';
            $transfer->save();

            Log::error("transfer ended with error: $reservation->id tran: $transfer->id status: $transfer->status");
            return;
        }

        $transfer->bank_id = $hotel_bank->bank_id;
        $transfer->account_name = $hotel_bank->bank->name;
        $transfer->account_number = $hotel_bank->bank->number;
        $transfer->currency = $hotel_bank->bank->currency;

        try {
            // khanbank code => 05 
            if ($hotel_bank->bank->code == '05') {
                $result = $this->khanbankService->transferDomestic(
                    $hotel_bank->number,
                    $reservation->amount,
                    'xroom-' . $reservation->id
                );
                $transfer->journal_code = $result['journalNo'];
                $transfer->status = 'success';
            } else {
                $bank_code = intval($hotel_bank->bank_code . '0000');
                $account_result = $this->khanbankService->getAccountName($hotel_bank->number, $bank_code);

                $result = $this->khanbankService->transferInterbank(
                    $hotel_bank->number,
                    $account_result['customer']['custLastName'] . $account_result['customer']['custFirstName'],
                    $reservation->amount,
                    $bank_code,
                    $hotel_bank->currency,
                    'xroom-' . $reservation->id
                );
                $transfer->journal_code = $result['journalNo'];
                $transfer->status = 'success';
            }
        } catch (\Exception $e) {
            Log::error('алдаа гарлаа ' . $e->getMessage());
            $transfer->code = '' . $e->getCode();
            $transfer->message = '' . $e->getMessage();
            $transfer->status = 'error';
        }

        $transfer->save();

        Log::info("transfer ended: $reservation->id tran: $transfer->id status: $transfer->status");
    }
}