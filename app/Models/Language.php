<?php

namespace App\Models;

class Language extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'languages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'name_national', 'locale',
        // 'translations'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
	protected $searchable = [
        'name', 'name_national',
	];
}
