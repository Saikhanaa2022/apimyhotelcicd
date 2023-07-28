<?php

namespace App\Models;
use App\Traits\HasTranslation;

class Service extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'countable' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'price', 'quantity', 'countable', 'bar_code', 'product_category_id', 'partner_id', 'service_category_id',
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'serviceCategory', 'productCategory', 'partner'
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
		return $this->hasMany('App\Models\ServiceTranslation', 'translation_id', 'id');
	}

    /**
     * Get the service category that owns the service.
     */
    public function productCategory()
    {
        return $this->belongsTo('App\Models\ProductCategory');
    }

    /**
     * Get the service category that owns the service.
     */
    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\ServiceCategory');
    }

    /**
     * Get the partner that owns the service.
     */
    public function partner()
    {
        return $this->belongsTo('App\Models\Partner');
    }

    /**
     * Get all of the clones for the service.
     */
    public function serviceClones()
    {
        return $this->hasMany('App\Models\ServiceClone');
    }
}
