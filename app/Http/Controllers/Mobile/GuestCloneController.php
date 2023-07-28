<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;

class GuestCloneController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\GuestClone';
    protected $request = 'App\Http\Requests\GuestCloneRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $this->model::whereIn('reservation_id', $hotel->reservations()->pluck('id'));
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
            'id', 'reservationId', 'name', 'surname', 'phoneNumber', 'email', 'passportNumber', 'nationality',
            'description', 'isBlacklist', 'blacklistReason'
        ]);

        $guest = \App\Models\Guest::firstOrCreate([
            'id' => $request->input('guest.id'),
            'hotel_id' => $request->hotel->id,
        ], $data);

        return array_merge($data, [
            'guestId' => $guest->id,
        ]);
    }
}
