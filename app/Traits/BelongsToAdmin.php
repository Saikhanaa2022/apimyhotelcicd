<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

trait BelongsToAdmin
{
    /**
     * Return paginated resources of specified property.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexByAdmin(Request $request)
    {
        $query = $this->model::query();

        if ($this->model === 'App\Models\Hotel') {
            $query->withCount('rooms');

            $hasStartDate = $request->filled('startDate');
            $hasEndDate = $request->filled('endDate');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $source = $request->input('source');

            $query->withCount(['reservations' => function ($query) use ($hasStartDate, $hasEndDate, $startDate, $endDate, $source) {
                // Check dates in range
                if ($hasStartDate && $hasEndDate) {
                    $query
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                } else if ($hasStartDate && !$hasEndDate) {
                    $query->whereDate('created_at', '>=', $startDate);
                } else if (!$hasStartDate && $hasEndDate) {
                    $query->whereDate('created_at', '<=', $endDate);
                }
                // Check source
                if (!is_null($source)) {
                    $query->whereHas('sourceClone', function ($q) use ($source) {
                        if ($source !== 'all' && $source !== 'other') {
                            $q->where('service_name', $source);
                        } else if ($source === 'other') {
                            $q->whereNull('service_name');
                        }
                    });
                }
                // Distinct group
                $query->select(DB::raw('count(distinct(group_id))'));
            }]);
        } else if ($this->model === 'App\Models\User') {
            $query->withCount('hotels')->where('sys_role', '<>', 'superadmin');
        } else if ($this->model === 'App\Models\HotelType') {
            $query->withCount('hotels');
        }

        return $this->index($request, $query);
    }

    /**
     * Return a resource object
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showByAdmin(Request $request, $id)
    {
        // Declare new model
        $model = new $this->model();

        $data = $this->model::query()
            ->where($model->getTable() . '.id', $id);

        if ($request->has('columns')) {
            $data->select($request->query('columns'));
        }

        $data = $data->first();

        if ($data) {
            // Load relations
            if ($request->has('with')) {
                $data->load($request->query('with'));
            }
        }

        return $this->responseJSON($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyByAdmin($id)
    {
        // Declare new model
        $model = new $this->model();

        $this->model::query()
            ->where($model->getTable() . '.id', $id)
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }
}
