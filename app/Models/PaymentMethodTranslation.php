<?php

namespace App\Models;
class PaymentMethodTranslation extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_method_translations';

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

   public function interval() {
       return $this->hasOne('App\Model\PaymentMethod');
   }
}
