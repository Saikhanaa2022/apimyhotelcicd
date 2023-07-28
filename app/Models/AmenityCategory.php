<?php

namespace App\Models;

use App\Traits\HasTranslation;

class AmenityCategory extends Base
{
	use HasTranslation;

	/**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'amenity_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name', 'image',
	];

    /**
     * The attributes that are translatable.
     *
     * @var array
     */
	public static $translatable = [
		'name',
	];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
	protected $appends = [
		// 'translation',
	];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
    ];

	/**
	 * The attributes that are appends with relation.
	 *
	 * @var array
	 */
	protected $with = [
        //
	];

    /**
     * Get the facilities for the facility category.
     */
    public function amenities()
    {
        return $this->hasMany('App\Models\Amenity');
    }
}
