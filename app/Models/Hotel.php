<?php

namespace App\Models;

use App\Traits\{HasIhotel, HasTranslation};

class Hotel extends Base
{
    use HasIhotel;
    use HasTranslation;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hotels';

    /**
     * The translation table associated with the model.
     *
     * @var boolean
     */
    protected $hasIhotel = true;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_test' => 'boolean',
        'is_auto_arrange' => 'boolean',
        'is_vatpayer' => 'boolean',
        'is_citypayer' => 'boolean',
        'has_time' => 'boolean',
        'has_online_book' => 'boolean',
        'has_chatbot' => 'boolean',
        'has_ihotel' => 'boolean',
        'has_xroom' => 'boolean',
        'is_show_payment' => 'boolean',
        'is_show_rules' => 'boolean',
        'images' => 'array',
        'location' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'register_no',
        'name',
        'working_date',
        'slug',
        'company_name',
        'hotel_type_id',
        'address',
        'email',
        'phone',
        'is_active',
        'is_test',
        'is_auto_arrange',
        'image',
        'check_in_time',
        'check_out_time',
        'is_vatpayer',
        'is_citypayer',
        'age_child',
        'has_time',
        'max_time',
        'district_id',
        'sync_id',
        'lat',
        'lng',
        'common_location_ids',
        'images',
        'star_rating',
        'description',
        'website',
        'res_email',
        'res_phone',
        'zip_code',
        'rules',
        'has_online_book',
        'has_chatbot',
        'has_ihotel',
        'has_xroom',
        'is_show_payment',
        'is_show_rules',
        'location'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_new',
        // 'ihotel',
        // 'translate',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
        'hotelType.name',
    ];

    /**
     * Get the translations for the model.
     * @return mixed
     */
    public function translations()
    {
        $result = $this->hasMany('App\Models\HotelTranslation', 'translation_id', 'id');
        return $result;
    }

    /**
     * Get the rate plan's type.
     *
     * @return string
     */
    public function getIsNewAttribute()
    {
        $totalRooms = $this->roomTypes()
            ->withCount('rooms')->get()->sum('rooms_count');
        return $totalRooms < 3;
    }

    /**
     * Get the rate plan's type.
     *
     * @return string
     */
    // public function getTotalRoomsAttribute()
    // {
    //     return $this->roomTypes()
    //         ->withCount('rooms')->get()->sum('rooms_count');
    // }

    /**
     * Get the users for the hotel.
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\User')->orderBy('is_default', 'DESC');
        ;
    }

    /**
     * Get the sources for the hotel.
     */
    public function sources()
    {
        return $this->hasMany('App\Models\Source')
            ->where('is_active', true)
            ->orderBy('is_default', 'DESC');
    }

    /**
     * Get the partners for the hotel.
     */
    public function partners()
    {
        return $this->hasMany('App\Models\Partner');
    }

    /**
     * Get the room types for the hotel.
     */
    public function roomTypes()
    {
        return $this->hasMany('App\Models\RoomType');
    }

    /**
     * Get the rooms for the hotel.
     */
    public function rooms()
    {
        return $this->hasManyThrough('App\Models\Room', 'App\Models\RoomType');
    }

    /**
     * Get the rate plans for the hotel.
     */
    public function ratePlans()
    {
        return $this->hasManyThrough('App\Models\RatePlan', 'App\Models\RoomType');
    }

    /**
     * Get the service categories for the hotel.
     */
    public function serviceCategories()
    {
        return $this->hasMany('App\Models\ServiceCategory');
    }

    /**
     * Get the services for the hotel.
     */
    public function services()
    {
        return $this->hasManyThrough('App\Models\Service', 'App\Models\ServiceCategory');
    }

    /**
     * Get the items for the hotel.
     */
    public function items()
    {
        return $this->hasManyThrough('App\Models\Item', 'App\Models\Reservation');
    }

    /**
     * Get the payment methods for the hotel.
     */
    public function paymentMethods()
    {
        return $this->hasMany('App\Models\PaymentMethod');
    }

    /**
     * Get the payments for the hotel.
     */
    public function payments()
    {
        return $this->hasManyThrough('App\Models\Payment', 'App\Models\Reservation');
    }

    /**
     * Get the charges for the hotel.
     */
    public function charges()
    {
        return $this->hasManyThrough('App\Models\Charge', 'App\Models\Reservation');
    }

    /**
     * Get the extra beds for the hotel.
     */
    public function extraBeds()
    {
        return $this->hasManyThrough('App\Models\ExtraBed', 'App\Models\Reservation');
    }

    /**
     * Get the currencies for the hotel.
     */
    public function currencies()
    {
        return $this->hasMany('App\Models\Currency');
    }

    /**
     * Get the reservations for the hotel.
     */
    public function groups()
    {
        return $this->hasMany('App\Models\Group');
    }

    /**
     * Get the reservations for the hotel.
     */
    public function reservations()
    {
        return $this->hasMany('App\Models\Reservation');
    }

    /**
     * Get the reservation payment methods for the hotel.
     */
    public function reservationPaymentMethods()
    {
        return $this->hasMany('App\Models\ReservationPaymentMethod');
    }

    /**
     * Get the reservation requests for the hotel.
     */
    public function reservationRequests()
    {
        return $this->hasMany('App\Models\ResReq');
    }

    /**
     * Get the guests for the hotel.
     */
    public function guests()
    {
        return $this->hasMany('App\Models\Guest');
    }

    /**
     * Get the hotel type associated with the hotel.
     */
    public function hotelType()
    {
        return $this->belongsTo('App\Models\HotelType');
    }

    /**
     * Get the banks for the hotel.
     */
    public function hotelBanks()
    {
        return $this->hasMany('App\Models\HotelBank');
    }

    /**
     * Get the rules for the hotel.
     */
    public function hotelRules()
    {
        return $this->hasMany('App\Models\HotelRule');
    }

    /**
     * Get the setting record associated with the user.
     */
    public function hotelSetting()
    {
        return $this->hasOne('App\Models\HotelSetting');
    }

    /**
     * Get the channels for the hotel.
     */
    public function channels()
    {
        return $this->belongsToMany('App\Models\Channel');
    }

    /**
     * Get the roles for the hotel.
     */
    public function roles()
    {
        return $this->hasMany('App\Models\Role');
    }

    /**
     * Get the extra bed policies for the hotel.
     */
    public function extraBedPolicies()
    {
        return $this->hasMany('App\Models\ExtraBedPolicy');
    }

    /**
     * Get the children policies for the hotel.
     */
    public function childrenPolicies()
    {
        return $this->hasMany('App\Models\ChildrenPolicy');
    }

    /**
     * Get the cancellation policies for the hotel.
     */
    public function cancellationPolicy()
    {
        return $this->hasOne('App\Models\CancellationPolicy');
    }

    /**
     * Get the taxes for the hotel.
     */
    public function taxes()
    {
        return $this->hasMany('App\Models\Tax');
    }

    /**
     * Get the invoices for the hotel.
     */
    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice');
    }

    /**
     * Get the district for the hotel.
     */
    public function district()
    {
        return $this->belongsTo('App\Models\District');
    }

    /**
     * Get the facilities for the hotel.
     */
    public function facilities()
    {
        return $this->belongsToMany('App\Models\Facility');
    }

    /**
     * The common locations that belong to the hotel.
     */
    public function commonLocations()
    {
        return $this->belongsToMany('App\Models\CommonLocation', 'hotel_location');
    }

    /**
     * Get the xroom types for the hotel.
     */
    public function xroomTypes()
    {
        return $this->hasMany('App\Models\XRoomRoomTypes');
    }

    /**
     * Get the xroom types for the hotel.
     */
    public function sourceRoomTypes()
    {
        return $this->hasMany('App\Models\SourceRoomTypes');
    }
}