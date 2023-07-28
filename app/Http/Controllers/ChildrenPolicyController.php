<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChildrenPolicyController extends BaseController
{
    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\ChildrenPolicy';
    protected $request = 'App\Http\Requests\ChilrenPolicyRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotel = request()->hotel;

        return $hotel->childrenPolicies();
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

        try {
            // Find user hotel
            $hotel = $request->hotel;

            if (!$request->filled('id')) {
                // Get hotel children policies
                $childrenPolicies = $hotel->childrenPolicies();

                // Check existing policies
                $check = $childrenPolicies->where('age_type', 'children')
                    ->where('min', '<=', $params['min'])
                    ->where('max', '>=', $params['min'])
                    ->get();

                if (count($check) > 0) {
                    return response()->json([
                        'message' => 'Алдаа гарлаа. Хүүхдийн үнийн бодлого үүсгэх боломжгүй байна.',
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
