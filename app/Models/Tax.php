<?php

namespace App\Models;
use App\Traits\HasTranslation;

class Tax extends Base
{
    use HasTranslation;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'taxes';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'inclusive' => 'boolean',
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'percentage', 'inclusive', 'hotel_id', 'is_default', 'is_enabled', 'key'
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
		return $this->hasMany('App\Models\TaxTranslation', 'translation_id', 'id');
	}

    /**
     * Get the tax's hotel.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the clones for the tax.
     */
    public function taxClones()
    {
        return $this->hasMany('App\Models\TaxClone');
    }
}
