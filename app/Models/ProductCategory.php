<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTranslation;

class ProductCategory extends Base
{
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'code', 'name',
    ];

    protected $appends = [
        'translate'
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
	public function translations()
	{
		return $this->hasMany('App\Models\ProductCategoryTranslation', 'translation_id', 'id');
	}

    /**
     * Get the services for the product category.
     */
    public function services()
    {
        return $this->hasMany('App\Models\Service');
    }
}
