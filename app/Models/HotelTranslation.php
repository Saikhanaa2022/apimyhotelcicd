<?php

namespace App\Models;
use App\Traits\{HasIhotel, HasTranslation};

class HotelTranslation extends Base
{
    use HasIhotel;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hotel_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'company_name', 'address'
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name', 'hotelType.name',
    ];

    public function hotel() {
        return $this->hasMany('App\Model\Hotel');
    }
}
