<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'items';

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
        'price', 'quantity', 'reservation_id', 'service_category_clone_id', 'service_clone_id', 'user_clone_id',
    ];

    /**
     * Append stay nights attribute
     */
    public function getAmountAttribute()
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the reservation associated with the item.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }

    /**
     * Get the service category clone associated with the item.
     */
    public function serviceCategoryClone()
    {
        return $this->belongsTo('App\Models\ServiceCategoryClone');
    }

    /**
     * Get the service clone associated with the item.
     */
    public function serviceClone()
    {
        return $this->belongsTo('App\Models\ServiceClone');
    }

    /**
     * Get the user clone associated with the item.
     */
    public function userClone()
    {
        return $this->belongsTo('App\Models\UserClone');
    }
}
