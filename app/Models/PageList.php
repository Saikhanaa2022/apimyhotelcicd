<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageList extends Model
{
    protected $table = 'pages';
    /**
     * The attributes that are searchable.
     *
     * @var array
     */
	protected $searchable = [
        'name'
	];

    public function helps()
    {
        return $this->belongsToMany('App\Models\Help', 'help_pages', 'page_id', 'help_id');
    }
}
