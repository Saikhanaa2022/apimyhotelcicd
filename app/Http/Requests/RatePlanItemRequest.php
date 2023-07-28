<?php

namespace App\Http\Requests;
use Illuminate\Validation\Rule;

class RatePlanItemRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function saveRules()
    {
        $id = request()->input('service.id');
        $ids = \App\Models\RatePlan::find(request()->input('ratePlan.id'))
            ->ratePlanItems()
            ->pluck('rate_plan_items.service_id');

        $serviceFilled = request()->filled('service.id');
        return [
            'price' => 'required|integer',
            'quantity' => 'required|integer',
            'ratePlan.id' => 'required|integer',
            'serviceCategory.id' => 'required|integer',
            'service.id' => 'required|integer',
            'service.productCategoryId' => ($serviceFilled ? 'required' : 'nullable') .'|integer',
            // Unique service
            'service.id' => Rule::unique('rate_plan_items', 'service_id')->where(function ($query) use ($ids) {
                return $query->whereIn('service_id', $ids);
            })->ignore($id),
        ];
    }
}
