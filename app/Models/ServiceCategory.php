<?php

namespace App\Models;
use App\Traits\HasTranslation;

class ServiceCategory extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_default', 'hotel_id',
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
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'translate'
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\ServiceCategoryTranslation', 'translation_id', 'id');
	}

    /**
     * Get the hotel that owns the service category.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the services for the service category.
     */
    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }

    /**
     * Get all of the clones for the service category.
     */
    public function serviceCategoryClones()
    {
        return $this->hasMany('App\Models\ServiceCategoryClone');
    }
}
