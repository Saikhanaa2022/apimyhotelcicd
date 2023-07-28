<?php

namespace App\Models;

class TaxClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tax_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'inclusive' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'is_default', 'is_enabled', 'key', 'percentage', 'inclusive', 'reservation_id', 'tax_id',
    ];
    
    /**
     * Get the reservation associated with the item.
     */
    public function reservation()
    {
        return $this->belongsTo('App\Models\Reservation');
    }

    /**
     * Get the tax associated with the tax clone.
     */
    public function tax()
    {
        return $this->belongsTo('App\Models\Tax');
    }
}
