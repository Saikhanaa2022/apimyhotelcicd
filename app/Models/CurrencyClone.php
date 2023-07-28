<?php

namespace App\Models;

class CurrencyClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'currency_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'short_name', 'rate', 'is_default', 'currency_id',
    ];

    /**
     * Get the currency associated with the currency clone.
     */
    public function currency()
    {
        return $this->belongsTo('App\Models\Currency');
    }
}
