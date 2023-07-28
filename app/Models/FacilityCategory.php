<?php

namespace App\Models;

// use App\Traits\HasTranslation;

class FacilityCategory extends Base
{
	// use HasTranslation;

	/**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'facility_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'name', 'image',
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
	 * The attributes that are appends with relation.
	 *
	 * @var array
	 */
	protected $with = [
        //
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
     * Get the facilities for the facility category.
     */
    public function facilities()
    {
        return $this->hasMany('App\Models\Facility');
    }

    /**
     * Get the translations for the facility category.
     */
 	// public function translations()
 	// {
 	// 	return $this->hasMany('App\Models\FacilityCategoryTranslation');
 	// }
}
