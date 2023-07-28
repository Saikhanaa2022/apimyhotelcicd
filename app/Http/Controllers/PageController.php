<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Traits\{BelongsToAdmin};

class PageController extends BaseController
{
    // use BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\PageList';
    protected $request = 'App\Http\Requests\PageRequest';

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

    public function index(Request $request, $customQuery = null)
    {
        // Search input
        $search = $request->query('search');
        // Pagination page length
        $rowsPerPage = (int) $request->query('rowsPerPage', 30);

        // Query
        $searchQuery = $this->newQuery();
        $current = '';
        $pageName = '';
        // PageName
        if ($request->has('name')) {
            $pageName = $this->currentPageName($request->query('name'));
            $current = $pageName;
            $searchQuery->whereHas('helps')->with('helps');
            // $searchQuery->whereHas('helps', function ($query) use ($pageName) {
            //     $query->where('slug', $pageName);
            // })->with('helps');
        }

        // Search
        $searchQuery->when($search, function ($query) use ($search) {
            return $query->with(['helps' => function ($query) use ($search) {
                return $query->search($search);
            }]);
        });

        if ($rowsPerPage === 0) {
            return response()->json($searchQuery->get());
        }

        if ($rowsPerPage === -1) {
            $rowsPerPage = $searchQuery->count();
        }
        $data = $searchQuery->paginate($rowsPerPage);
        $filtered = 0;
        foreach ($data as $key => $value) {
            if ($value->slug === $current) {
                $filtered = $key;
            }
        }
        // $filtered = $data->filter(function ($value, $key) use ($current) {
        //     if ($value->slug === $current) {
        //         return $key;
        //     } else {
        //         return 0;
        //     }
        // });

        // return response()->json($data);
        return response()->json([$data, $filtered]);
    }

    private function currentPageName($params)
    {
        $string = $params;
        $string = explode('__', $string);
        array_pop($string);
        $string = implode($string);
        return $string;
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
}
