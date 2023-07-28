<?php

namespace App\Http\Controllers\Mobile;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Get a validator for an incoming request.
     *
     * @param  array  $data
     * @param  string  $action
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($data, $action, $messages = [])
    {
        Validator::make($data, call_user_func([$this->request, $action]), $messages)
            ->validate();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::where('hotel_id', request()->hotel->id);
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
        $data = $request->all();
        $data = array_merge($data, [
            'hotelId' => $request->hotel->id,
        ]);

        return $data;
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
        //
    }

    /**
     * Return JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function responseJSON($data, $status = 200)
    {
        $className = class_basename($this->model);

        return response()->json([
            camel_case($className) => $data,
        ], $status);
    }

    /**
     * Return a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, $customQuery = null)
    {
        // Search input
        $search = $request->query('search');
        // Pagination page length
        $rowsPerPage = (int) $request->query('rowsPerPage', 25);
        // Sort column
        $sortBy = snake_case($request->query('sortBy', 'id'));
        // Sort direction
        $direction = $request->query('direction', 'desc');

        // Query
        $searchQuery = $customQuery ? $customQuery : $this->newQuery();

        // Custom filter
        $searchQuery = $this->filter($searchQuery, $request);

        // Search
        $searchQuery->when($search, function ($query, $search) {
            return $query->search($search);
        });

        // Select columns
        if ($request->has('columns')) {
            $searchQuery->select($request->query('columns'));
        }

        // Load relations
        if ($request->has('with')) {
            $searchQuery->with($request->query('with'));
        }

        // Load with counts model
        if ($request->has('withCounts')) {
            $searchQuery->withCount($request->query('withCounts'));
        }

        // Sort
        $searchQuery->orderBy($sortBy, $direction);

        if ($rowsPerPage === 0) {
            return response()->json($searchQuery->get());
        }

        if ($rowsPerPage === -1) {
            $rowsPerPage = $searchQuery->count();
        }

        return response()->json($searchQuery->paginate($rowsPerPage));
    }

    /**
     * Return a resource object
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        // Declare new model
        $model = new $this->model();
        $data = $this->newQuery();

        // Select columns
        if ($request->has('columns')) {
            $data->select($request->query('columns'));
        }

        $data = $data->where($model->getTable() . '.id', $id);
        // Load relations
        if ($request->has('with')) {
            $data->with($request->query('with'));
        }

        // Load single relation
        if ($request->has('withSingle')) {
            $data->with($request->query('withSingle'));
        }

        $data = $data->first();

        if (is_null($data)) {
            return $this->responseJSON($data, 404);
        }

        return $this->responseJSON($data);
    }

    /**
     * Store or update the resource in storage.
     *
     * @param  array  $array
     * @return $data
     */
    protected function storeOrUpdate(array $array)
    {
        $params = snakeCaseKeys($array);

        if (array_key_exists('id', $params) && $params['id']) {
            // Declare new model
            $model = new $this->model();

            // Find model
            $data = $this->newQuery()
                ->where($model->getTable() . '.id', $params['id'])
                ->firstOrFail();
            $data->update($params);

            // Model check to must connect the "ihotel_db" database
            // $hasIhotel = isset($data->ihotel) ? $data->ihotel : false;

            // if ($hasIhotel && env('SYNC_IHOTEL') == true) {
            //     // Connect and Update to IHOTEL
            //     $syncId = $data->sync_id;
            //     unset($params['id']);
            //     $data->on('ihotel_db')->where('sync_id', $syncId)->update($params);
            // }
        } else {
            $data = $this->model::create($params);

            // Model check to must connect the "ihotel_db" database
            // $hasIhotel = isset($data->ihotel) ? $data->ihotel : false;

            // if ($hasIhotel && env('SYNC_IHOTEL') == true) {
            //     $data->sync_id = $data->id;
            //     $data->save();
            //     // Connect and Insert to IHOTEL
            //     $dataUpdate = $this->model::where('id', $data->id)->update(['sync_id' => $data->id]);
            //     $params['sync_id'] = $data->id;
            //     $data->on('ihotel_db')->create($params);
            // }
            $data->refresh();
        }

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

        return $this->responseJSON($data);
    }

    /**
     * Before resource deleted.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function beforeDelete($model)
    {
        // 
    }

    /**
     * Remove the specified resources from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        // Declare new model
        $model = new $this->model();

        $this->newQuery()
            ->whereIn($model->getTable() . '.id', $request->input('ids'))
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Declare new model
        $model = new $this->model();

        $data = $this->newQuery()
            ->where($model->getTable() . '.id', $id)
            ->first();

        if (!is_null($data)) {
            $this->beforeDelete($data);

            $data->delete();

            $this->afterDelete($data);

            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }

    /**
     * After resource deleted.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function afterDelete($model)
    {
        // 
    }
}
