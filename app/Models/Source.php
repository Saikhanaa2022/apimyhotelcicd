<?php

namespace App\Models;
// use App\Traits\HasTranslation;

class Source extends Base
{
    // use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sources';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'short_name', 'color', 'is_default', 'is_active', 'service_name', 'hotel_id',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'short_name',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        // 'translate'
    ];

    // /**
    //  * Get the translations for the model.
    //  * @return mixed
    //  */
	// public function translations()
	// {
	// 	return $this->hasMany('App\Models\SourceCategoryTranslation', 'translation_id', 'id');
	// }

    /**
     * Get the sources's hotel.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get all of the clones for the source.
     */
    public function sourceClones()
    {
        return $this->hasMany('App\Models\SourceClone');
    }

    /**
     * Get all of the roomTypes for the source.
     */
    public function roomTypes()
    {
        return $this->hasMany('App\Models\SourceRoomTypes');
    }
}
