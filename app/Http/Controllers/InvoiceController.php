<?php

namespace App\Http\Controllers;

use App\Models\{Invoice, InvoiceItem};
use App\Events\EmailInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Invoice';
    protected $request = 'App\Http\Requests\InvoiceRequest';

    /*
    *
    * invoice send mail
    *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function sendMail(Request $request) {

        // return $request;
        $this->validate(
            $request, 
            ['email' => 'required|email|max:255'], 
            ['required' => 'Хоосон утга дэмжихгүй']
        );
        // Get mail data
        $id = $request->id;
        $email = $request->email;

        // MySQL transaction
        DB::beginTransaction();

        try {
            // Invoice Email update
            $invoice = Invoice::find($id);
            $invoice->email = $email;
            $invoice->is_sent = 1;
            $invoice->save();

            // Invoice send mail event
            event(new EmailInvoice($email, $invoice));

            // Commit transaction
            DB::commit();

            return response()->json([
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }

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
            if ($filterType === 'invoiceDate') {
                $filterField = 'invoice_date';
            } else if ($filterType === 'paymentPeriod') {
                $filterField = 'payment_period';
            } else {
                $filterField = '';
            }

            if ($request->filled('startDate') && $request->filled('endDate')) {
                $query
                    ->whereDate($filterField, '>=', date($request->input('startDate')))
                    ->whereDate($filterField, '<=', date($request->input('endDate')));
            } else if ($request->filled('startDate') && !$request->filled('endDate')) {
                $query->whereDate($filterField, '>=', date($request->input('startDate')));
            } else if (!$request->filled('startDate') && $request->filled('endDate')) {
                $query->whereDate($filterField, '<=', date($request->input('endDate')));
            }
        }

        if ($request->filled('isSent')) {
            $query->whereIsSent($request->input('isSent'));
        }

        return $query;
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->only([
            'groupId', 'email', 'guestName', 'guestSurname', 'items'
        ]);

        // Get hotel from request
        $hotel = $request->hotel;

        $hotelBanks = [];
        foreach ($hotel->hotelBanks as $item) {
            array_push($hotelBanks, [
                'bank' => $item->bank->name,
                'accountName' => $item->account_name,
                'number' => $item->number,
                'currency' => $item->currency,
            ]);
        }

        $data = array_merge($data, [
            'hotelId' => $hotel->id,
            'reservationId' => $request->input('resId'),
            'reservationNumber' => $request->input('resNumber'),
            'hotelImage' => $hotel->image,
            'hotelName' => $hotel->name,
            'hotelRegisterNo' => $hotel->register_no,
            'hotelAddress' => $hotel->address,
            'hotelPhoneNumber' => $hotel->phone,
            'hotelEmail' => $hotel->email,
            'hotelCompanyName' => $hotel->company_name,
            'hotelBanks' => json_encode($hotelBanks, JSON_UNESCAPED_UNICODE),
            'customerName' => $request->input('customer.customerName'),
            'registerNo' => $request->input('customer.registerNo'),
            'address' => $request->input('customer.address'),
            'phoneNumber' => $request->input('customer.phoneNumber'),
            'contractNo' => $request->input('customer.contractNo'),
            'tourCode' => $request->input('customer.tourCode'),
            'voucherCode' => $request->input('customer.voucherCode'),
            'invoiceDate' => $request->input('customer.invoiceDate'),
            'paymentPeriod' => $request->input('customer.paymentPeriod')
        ]);

        return $data;
    }

    /**
     * Store or update the resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'saveRules');

        $data = $this->storeOrUpdate($this->requestParams($request));

        $this->afterCommit($request, $data);

        return response()->json([
            'id' => $data->id,
        ]);
    }

    /**
     * After new resource created.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function afterCommit(Request $request, $model)
    {
        if ($request->filled('items')) {
            foreach ($request->input('items') as $item) {
                // Create payment items
                InvoiceItem::create([
                    'invoice_id' => $model->id,
                    'date' => $item['date'],
                    'name' => $item['name'],
                    'item_type' => $item['type'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }
    }
}
