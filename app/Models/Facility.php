<?php

namespace App\Models;

// use App\Traits\HasTranslation;

class Facility extends Base
{
	// use HasTranslation;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	public $table = 'facilities';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'image', 'facility_category_id',
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
        'name',
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
	// public static function translationRules()
	// {
	// 	return [
	// 		'name' => 'required|string|max:255',
	// 	];
	// }

    /**
     * Get the translations for the facility.
     */
	// public function translations()
	// {
	// 	return $this->hasMany('App\Models\FacilityTranslation');
	// }

    /**
     * Get the facility category that owns the facility.
     */
	public function facilityCategory()
	{
		return $this->belongsTo('App\Models\FacilityCategory');
	}

	/**
     * Get the hotels that belong to the facility.
     */
	public function hotels()
    {
        return $this->belongsToMany('App\Models\Hotel');
    }
}
