<?php

namespace App\Models;

class HotelSetting extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'hotel_settings';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'has_night_audit' => 'boolean',
        'is_nightaudit_auto' => 'boolean',
        'is_must_pay' => 'boolean',
        'bcc_emails' => 'array',
        'email_attachments' => 'array',
        'has_res_request' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $fillable = [
		'hotel_id', 'has_night_audit', 'is_nightaudit_auto', 'night_audit_time', 'has_res_request', 'discount_calc_type',
        'is_must_pay', 'bcc_emails', 'email_header', 'email_footer', 'email_body', 'email_contact', 'email_attachments'
	];

	/**
	 * The attributes that are appends with relation.
	 *
	 * @var array
	 */
	protected $with = [
        //
	];

    /**
     * Get the hotels for the hotel type.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }
}
