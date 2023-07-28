<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatePlanItem extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rate_plan_items';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'amount',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'price', 'quantity', 'rate_plan_id', 'service_category_id', 'service_id',
    ];

    /**
     * Append stay nights attribute
     */
    public function getAmountAttribute()
    {
        return $this->price * $this->quantity;
    }
    
    /**
     * Get the rate plan associated with the item.
     */
    public function ratePlan()
    {
        return $this->belongsTo('App\Models\RatePlan');
    }
    
    /**
     * Get the service category clone associated with the item.
     */
    public function serviceCategory()
    {
        return $this->belongsTo('App\Models\ServiceCategory');
    }
    
    /**
     * Get the service clone associated with the item.
     */
    public function service()
    {
        return $this->belongsTo('App\Models\Service');
    }
}
