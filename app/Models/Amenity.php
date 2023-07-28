<?php

namespace App\Models;
use App\Traits\HasTranslation;

class Amenity extends Base
{
	use HasTranslation;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	public $table = 'amenities';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'name_en', 'image', 'amenity_category_id',
	];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'pivot',
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
        'name', 'name_en',
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
     * Get the amenity category that owns the amenity.
     */
	public function amenityCategory()
	{
		return $this->belongsTo('App\Models\amenityCategory');
	}

	/**
     * Get the room types that belong to the amenity.
     */
	public function roomTypes()
    {
        return $this->belongsToMany('App\Models\RoomType');
    }
}
