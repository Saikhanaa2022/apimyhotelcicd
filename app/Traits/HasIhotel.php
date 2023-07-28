<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasIhotel
{
    /**
     * Scope a query to search eloquents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getIhotelAttribute()
    {
        return $this->hasIhotel;
    }
}
