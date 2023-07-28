<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExtraBedPolicyController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\ExtraBedPolicy';
    protected $request = 'App\Http\Requests\ExtraBedPolicyRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->extraBedPolicies();
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

        $params = $request->only([
            'id', 'ageType', 'priceType', 'price', 'min', 'max',
        ]);
        
        if ($params['priceType'] !== 'free') {
            // Validation
            $request->validate([
                'price' => 'gt:0',
            ]);
        }

        $ageType = $params['ageType'];

        try {
            // Find user hotel
            $hotel = $request->hotel;

            if (!$request->filled('id')) {
                // Get hotel extra bed policies
                $extraBedPolicies = $hotel->extraBedPolicies();

                // Check existing policies
                if($ageType === 'children') {
                    // $check = $extraBedPolicies->where('age_type', $ageType)
                    //     ->whereBetween('min', [$params['min'], $params['max']])
                    //     ->orWhereBetween('max', [$params['min'], $params['max']])
                    //     ->get();
                    $check = $extraBedPolicies->where('age_type', $ageType)
                        ->where('min', '<=', $params['min'])
                        ->where('max', '>=', $params['min'])
                        ->get();
                } else if($ageType === 'any') {
                    $check = $extraBedPolicies->whereIn('age_type', ['children', 'adults', 'any'])->get();
                } else {
                    $check = $extraBedPolicies->where('age_type', '!=', 'children')->where('max', $params['max'])->get();
                }

                if (count($check) > 0) {
                    return response()->json([
                        'message' => 'Алдаа гарлаа. Нэмэлт орны тохиргоо үүсгэх боломжгүй байна.',
                        'data' => json_encode($check),
                    ], 400);
                }
            }

            $params = array_merge($params, [
                'hotelId' => $hotel->id,
            ]);

            $data = $this->storeOrUpdate($params);
            // $data = $params;

            return $this->responseJSON($data);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'Алдаа гарлаа. Та дахин оролдоно уу.',
            ], 400);
        }
    }
}
