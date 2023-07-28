<?php

namespace App\Models;
use App\Traits\HasTranslation;

class Help extends Base
{
    use HasTranslation;

	/**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'helps';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'icon', 'url', 'content', 'page_id'
    ];

    // protected $with = [
    //     'pages'
    // ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
	protected $searchable = [
        'name'
	];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'translate', 'pages'
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\HelpTranslation', 'translation_id', 'id');
	}

    public function pages()
    {
        return $this->belongsToMany('App\Models\PageList', 'help_pages', 'help_id', 'page_id');
    }

    public function getPagesAttribute()
    {
        return $this->pages()->pluck('page_id');
    }
}
