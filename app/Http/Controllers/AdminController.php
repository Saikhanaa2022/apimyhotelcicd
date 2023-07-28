<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\{Hotel, User, Room, RoomType};

class AdminController extends Controller
{
    /**
     * Return counts statistics.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function countReport(Request $request)
    {
        return response()->json([
            'hotelsCount' => $this->hotelsCount($request),
            'hotelsActiveCount' => $this->hotelsActiveCount($request),
            'usersCount' => $this->usersCount($request),
            'roomsCount' => $this->roomsCount($request),
        ]);
    }
    
    /**
     * Нийт бүртгэгдсэн буудлын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function hotelsCount(Request $request)
    {
        return Hotel::count();
    }

    /**
     * Нийт идэвхтэй буудлын тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function hotelsActiveCount(Request $request)
    {
        return Hotel::where('is_active', 1)->count();
    }

    /**
     * Нийт хэрэглэгчийн тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function usersCount(Request $request)
    {
        return User::count();
    }

    /**
     * Нийт өрөөний тоо
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function roomsCount(Request $request)
    {
        return Room::count();
    }
}
