<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait Searchable
{
    /**
     * Scope a query to search eloquents.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            foreach ($this->searchable as $column) {
                if (Str::contains($column, '.')) {
                    // Relation pointer
                    $relation = beforeLast($column, '.');
                    // Column
                    $value = afterLast($column, '.');

                    // Search relations
                    $query->orWhereHas($relation, function ($query) use ($search, $value) {
                        $query->where($value, 'LIKE', '%' . $search . '%');
                    });
                } else {
                    $query->orWhere($column, 'LIKE', '%' . $search . '%');
                }
            }
        });
    }
}
