<?php

namespace App\Models;

use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Base extends Model
{
    use Searchable;

    /**
     * Scope a query to merge whereHas and with.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAndWhereHas($query, $relation, $callback)
    {
        return $query->whereHas($relation, $callback)
            ->with([
                $relation => $callback,
            ]);
    }

    /**
     * Return all fillables.
     * @return array
     */
    public function hasIhotel()
    {
        // dd($this->ihotel);
        return isset($this->ihotel) ? $this->ihotel : false;
    }
        
    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        //
    ];

    /**
     * Return all fillables.
     * @return array
     */
    public function translationTblName()
    {
        return isset($this->translationTable) ? $this->translationTable : null;
    }
    
    /**
     * Get Translation from TABLE
     *
     * @return mixed
     */
    protected function translationWhereLocale($query, $tblName = null) {
        $requestLocale = config('app.locale');
        return $query->where($tblName ? $tblName.'.locale' : 'locale', 'en');
        // $resultOfLocale = $query->where($tblName ? $tblName.'.locale' : 'locale', $requestLocale);
        // $count = $resultOfLocale->count();
        // if ($count > 0 ) {
        //     return $resultOfLocale;
        // } else {
        //     return $query->orWhere($tblName ? $tblName.'.locale' : 'locale', env('DEFAULT_LANGUAGE'));
        // }
    }
}
