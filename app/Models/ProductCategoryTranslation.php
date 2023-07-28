<?php

namespace App\Models;
class ProductCategoryTranslation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_category_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
    ];

   public function productCategory() {
       return $this->hasOne('App\Model\ProductCategory');
   }
}
