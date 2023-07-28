<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait BelongsToHotel
{
    /**
     * Return paginated resources of specified property.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexByHotel(Request $request)
    {
        $id = $request->hotel->id;
        $query = $this->model::whereHas('hotel', function ($query) use ($id) {
            $query->where('id', $id);
        });

        return $this->index($request, $query);
    }
}
