<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RatePlanController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\RatePlan';
    protected $request = 'App\Http\Requests\RatePlanRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;
            
        return $hotel->ratePlans();
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
            'id', 'name', 'nonRef', 'isOta', 'isOnlineBook'
        ]);

        return array_merge($data, [
            'roomTypeId' => $request->input('roomType.id'),
            'isDaily' => $request->input('type') === 'daily',
        ]);
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
        $this->validator($request->all(), 'saveRules', [
            'isOta.unique' => 'Зөвхөн 1 үнийн төрөл дээр идэвхжүүлэх боломжтой',
            'isOnlineBook.unique' => 'Зөвхөн 1 үнийн төрөл дээр идэвхжүүлэх боломжтой',
        ]);

        $data = $this->storeOrUpdate($this->requestParams($request));

        $partnerIds = collect($request->input('partners'))
            ->pluck('id');

        $mealIds = collect($request->input('meals'))
            ->pluck('id');

        // Sync partners
        $data->partners()->sync($partnerIds);

        // Sync meals
        $data->meals()->sync($mealIds);

        $this->afterCommit($request, $data);

        return $this->responseJSON($data);
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
        if(!$request->filled('id')) {
            // Get rate plan of room type occupancy
            $occupancy = $model->roomType->occupancy;

            for($i = $occupancy; $i > 0; $i--) {
                // Creating occupancy rate plan for the rate plan
                \App\Models\OccupancyRatePlan::create([
                    'occupancy' => $i,
                    'discount_type' => 'currency',
                    'discount' => 0,
                    'is_default' => $i == $occupancy ? true : false,
                    'is_active' => false,
                    'rate_plan_id' => $model->id,
                ]);
            }
        }
    }

    /**
     * Return daily rates by rate plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRates(Request $request, $id)
    {
        // Validation
        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
        ]);

        $ratePlan = $this->newQuery()
            ->where('rate_plans.id', $id)
            ->firstOrFail();

        if ($ratePlan->is_daily) {
            $rates = $ratePlan->dailyRates()
                ->whereBetween('date', [
                    $request->query('from'), $request->query('to'),
                ])
                ->get();
        } 
        // else {
        //     $intervals = $ratePlan->intervals()
        //         ->where([
        //             ['start_date', '<=', $request->query('to')],
        //             ['end_date', '>=', $request->query('from')],
        //         ])
        //         ->get();

        //     // Empty rates
        //     $rates = [];

        //     // Loop intervals
        //     foreach ($intervals as $interval) {
        //         // Loop interval's rates
        //         foreach ($interval->rates as $rate) {
        //             // Day of week
        //             $days = [
        //                 $rate->day_of_week,
        //             ];

        //             // Get all dates
        //             $dates = getDatesFromRange(
        //                 $interval->start_date,
        //                 $interval->end_date,
        //                 $days
        //             );

        //             foreach ($dates as $date) {
        //                 array_push($rates, [
        //                     'date' => $date->format('Y-m-d'),
        //                     'value' => $rate->value,
        //                     'min_los' => $interval->min_los,
        //                     'max_los' => $interval->max_los,
        //                 ]);
        //             }
        //         }
        //     }
        // }

        return response()->json([
            'rates' => $rates,
        ]);
    }

    /**
     * Return daily rates by rate plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRatesByRoomType(Request $request, $id)
    {
        // Validation
        $request->validate([
            'from' => 'required|date_format:Y-m-d',
            'to' => 'required|date_format:Y-m-d',
        ]);

        $roomType = $request->hotel->roomTypes()
            ->where('id', $id)
            ->firstOrFail();

        // if ($ratePlan->is_daily) {
            $rates = $ratePlan->dailyRates()
                ->whereBetween('date', [
                    $request->query('from'), $request->query('to'),
                ])
                ->get();
        // } 

        $rates = $roomType->ratePlans()
            ->where([
                ['date', '>=', $start],
                ['date', '<=', $end],
            ])
            ->get();

        return response()->json([
            'rates' => $rates,
        ]);
    }

    /**
     * Store or update the daily rates in storage..
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDailyRates(Request $request, $id)
    {
        // Validate request
        $this->validator($request->all(), 'saveDailyRatesRules', [
            'days.required' => 'Үнэ тохируулах гараг сонгоно уу'
        ]);

        $ratePlan = $this->newQuery()
            ->where('rate_plans.id', $id)
            ->firstOrFail();

        // Get all dates
        $dates = getDatesFromRange(
            $request->input('startDate'),
            $request->input('endDate'),
            $request->input('days')
        );

        if ($request->filled('rate')) {
            $ratePlan->dailyRates()
                ->whereIn('date', $dates)
                ->update([
                    'value' => $request->input('rate'),
                    'min_los' => $request->input('minLos'),
                    'max_los' => $request->input('maxLos'),
                ]);

            $existDates = $ratePlan->dailyRates()
                ->whereIn('date', $dates)
                ->pluck('date')
                ->toArray();

            $filtered = collect($dates)->filter(function ($date) use ($existDates) {
                return !in_array($date->format('Y-m-d'), $existDates);
            })->toArray();

            $insertData = [];
            // Prepare for insert query
            foreach ($filtered as $date) {
                array_push($insertData, [
                    'date' => $date,
                    'value' => $request->input('rate'),
                    'min_los' => $request->input('minLos'),
                    'max_los' => $request->input('maxLos'),
                    'rate_plan_id' => $id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            // Create daily rates
            DB::table('daily_rates')
                ->insert($insertData);
        } else {
            // Delete
            $ratePlan->dailyRates()
                ->whereIn('date', $dates)
                ->delete();
        }
        
        $dailyRates = $ratePlan->dailyRates()
            ->whereIn('date', $dates)
            ->get();

        return response()->json([
            'dailyRates' => $dailyRates,
        ]);
    }

    /**
     * Store or update the occupancy rates in storage..
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOccupancyRatePlan(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'updateOccupancyPlansRules');

        // Get occupancy rate plans from request
        $occupancyRatePlans = $request->input('occupancyRatePlans');

        foreach($occupancyRatePlans as $orp) {
            $occupancyRatePlan = \App\Models\OccupancyRatePlan::find($orp['id']);
            $occupancyRatePlan->discount_type = $orp['discountType'];
            $occupancyRatePlan->discount = $orp['discount'];
            $occupancyRatePlan->is_active = $orp['isActive'];
            $occupancyRatePlan->update();
        }

        return response()->json([
            'success' => true,
        ]);
    }
}
