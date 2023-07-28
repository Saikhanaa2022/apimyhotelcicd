<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\{BelongsToAdmin};
use Illuminate\Support\Str;
use App\Models\PageList;

class HelpController extends BaseController
{
    use BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\Help';
    protected $request = 'App\Http\Requests\HelpRequest';

    /**
     * Get a new query builder for the model's table.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        return $this->model::query();
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        return $request->all();
    }

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
            $data->pages()->sync($params['pages']);

        } else {
            $data = $this->model::create($params);
            $data->pages()->attach(1);

            $data->refresh();
        }

        return $data;
    }

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

    // public function listWithPage(Request $request, $customQuery = null)
    // {
    //     // Search input
    //     $search = $request->query('search');
    //     // Pagination page length
    //     $rowsPerPage = (int) $request->query('rowsPerPage', 9);

    //     // Query
    //     $searchQuery = $this->newQuery();

    //     // PageName
    //     if ($request->has('name')) {
    //         $pageName = $this->currentPageName($request->query('name'));
    //         $searchQuery = PageList::whereHas('helps')->with('helps');
    //         // $searchQuery->with('page')->whereHas('page', function ($query) use ($pageName) {
    //         //     $query->where('slug', $pageName);
    //         // });
    //     }

    //     // Search
    //     $searchQuery->when($search, function ($query, $search) {
    //         return $query->search($search);
    //     });

    //     if ($rowsPerPage === 0) {
    //         return response()->json($searchQuery->get());
    //     }

    //     if ($rowsPerPage === -1) {
    //         $rowsPerPage = $searchQuery->count();
    //     }

    //     return response()->json($searchQuery->paginate($rowsPerPage));
    // }

    private function currentPageName($params)
    {
        $string = $params;
        $string = explode('__', $string);
        array_pop($string);
        $string = implode($string);
        return $string;
    }
}
