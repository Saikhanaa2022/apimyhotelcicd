<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Carbon\Carbon;

class GroupController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Group';
    protected $request = 'App\Http\Requests\GroupRequest';

    /**
     * Return group reservations
     * FIX HERE 2020/09/19
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupReservation(Request $request, $groupId, $resId)
    {
        $data = $this->newQuery()
            ->where('id', $groupId)
            ->firstOrFail();

        // Load other relations
        if ($request->has('with')) {
            $data->load(array_merge($request->query('with'), ['reservations' => function ($query) use ($resId) {
                $query->where('id', $resId);
            }]));
        }

        return $this->responseJSON($data);
    }

    /**
     * Return group payments data
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupPayments(Request $request, $groupId)
    {
        $group = $this->newQuery()
            ->where('id', $groupId)
            ->firstOrFail();

        $items = [];
        $totalAmount = 0;
        $totalAmountTax = 0;
        $firstRes = $group->reservations[0];

        foreach ($group->reservations as $res) {
            $totalAmount += $res->occupancyAmount() + $res->itemsAmount() + $res->extraBedsAmount();
            $totalAmountTax += $res->amount;
            $quantity = $res->is_time ? 1 : $res->stay_nights;

            // Push room
            $items[] = [
                'date' => Carbon::parse($res->check_in)->format('y/m/d' . ($res->is_time ? ' H:m' : '')) . ' - ' . Carbon::parse($res->check_out)->format('y/m/d' . ($res->is_time ? ' H:m' : '')),
                'name' => $res->roomTypeClone->name,
                'quantity' => $quantity,
                'price' => $res->occupancyAmount() / $quantity,
                'totalPrice' => $res->occupancyAmount()
            ];

            // Push services
            foreach ($res->items as $item) {
                $items[] = [
                    'date' => Carbon::parse($item->created_at)->format('y/m/d H:m'),
                    'name' => $item->serviceCategoryClone->name . ' - ' . $item->serviceClone->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'totalPrice' => $item->price * $item->quantity
                ];
            }

            // Push extra beds
            foreach ($res->extraBeds as $item) {
                $items[] = [
                    'date' => Carbon::parse($item->created_at)->format('y/m/d H:m'),
                    'name' => 'Нэмэлт ор',
                    'quantity' => $item->nights,
                    'price' => $item->amount,
                    'totalPrice' => $item->amount * $item->nights
                ];
            }
        }

        $data = [
            'groupId' => $group->id,
            'groupNumber' => $group->number,
            'guest' => $firstRes->guestClone,
            'items' => $items,
            'taxes' => $firstRes->taxClones,
            'totalAmount' => $totalAmount,
            'totalAmountTax' => $totalAmountTax
        ];

        return response()->json($data, 200);
    }
}
