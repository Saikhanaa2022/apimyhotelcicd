<?php

namespace App\Models;

class CancellationPolicy extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cancellation_policies';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_free' => 'boolean',
        'has_prepayment' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'policy_summary',
        'prepayment_summary',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hotel_id', 'cancellation_time_id', 'cancellation_percent_id', 'is_free', 'addition_percent_id', 'has_prepayment'
    ];

    /**
     * Get policy summary
     */
    public function getPolicySummaryAttribute()
    {
        $summary = '';
        $textDay = '';
        $textPercent = '';
        $textAdditionPercent = '';

        if ($this->is_free) {
            // Free cancellation
            if ($this->cancellationTime->has_time) {
                $summary = 'Зочин ирэх өдрийн ' . $this->cancellationTime->day . ' цагаас өмнө захиалгаа үнэгүй цуцлах боломжтой.';
                $textDay = 'Зочин ирэх өдрийн ' . $this->cancellationTime->day . ' цагаас хойш';
            } else {
                $summary = 'Зочин ирэхээсээ өмнөх ' . $this->cancellationTime->day . ' хоног хүртэлх хугацаанд захиалгаа үнэгүй цуцлах боломжтой.';
                $textDay = 'Зочин ирэхээсээ өмнөх ' . $this->cancellationTime->day .' хоногийн дотор';
            }
            
            // Not free
            $textPercent = 'нийт үнийн ' . $this->cancellationPercent->percent .'% -тай';
            if ($this->cancellationPercent->is_first_night) {
                $textPercent = 'эхний шөнийн төлбөртэй';
            }

            // No show
            $textAdditionPercent = 'нийт үнийн ' . $this->cancellationAdditionPercent->percent .'% -тай';
            if ($this->cancellationAdditionPercent->is_first_night) {
                $textAdditionPercent = 'эхний шөнийн төлбөртэй';
            }

            $textAdditionPercent = '<br> Зочин ирээгүй тохиолдолд ' . $textAdditionPercent . ' тэнцэх торгууль төлнө.';

            // Summary
            $summary = $summary . '<br>' . $textDay . ' захиалгаа цуцлах бол ' . 
                $textPercent . ' тэнцэх торгууль төлнө.' . $textAdditionPercent;
        } else {
            // Not free
            if ($this->cancellationTime) {
                $summary = 'Зочин ирэхээсээ өмнөх ' . $this->cancellationTime->day . ' хоног хүртэлх хугацаанд захиалгаа цуцлах бол';
                // $textDay = $this->cancellationTime->day
                $textAdditionPercent = '<br>Зочин ирэхээсээ өмнөх ' . $this->cancellationTime->day .' хоногийн дотор захиалгаа цуцлах бол нийт үнийн ' 
                . $this->cancellationAdditionPercent->percent . '% -тай тэнцэх торгууль төлнө.';
            } else {
                $summary = 'Зочин захиалгаа цуцлах бол';
            }

            if ($this->cancellationPercent) {
                $textPercent = 'нийт үнийн ' . $this->cancellationPercent->percent .'% -тай';
                if ($this->cancellationPercent->is_first_night) {
                    $textPercent = 'эхний шөнийн төлбөртэй';
                }
            }

            $summary = $summary . ' ' . $textPercent . ' тэнцэх торгууль төлнө.' . $textAdditionPercent;
        }

        return $summary;
    }

    /**
     * Get prepayment summary.
     */
    public function getPrepaymentSummaryAttribute()
    {
        return 'Урьдчилсан төлбөр ' . ($this->has_prepayment ? 'авна.' : 'авахгүй.');
    }

    /**
     * Get the hotel associated with the policy.
     */
    public function hotel()
    {
        return $this->belongsTo('App\Models\Hotel');
    }

    /**
     * Get the cancellation clones associated with the policy.
     */
    public function cancellationPolicyClones()
    {
        return $this->hasMany('App\Models\CancellationPolicyClone');
    }

    /**
     * Get the cancellation time associated with the policy.
     */
    public function cancellationTime()
    {
        return $this->belongsTo('App\Models\CancellationTime');
    }

    /**
     * Get the cancellation percent associated with the policy.
     */
    public function cancellationPercent()
    {
        return $this->belongsTo('App\Models\CancellationPercent');
    }

    /**
     * Get the cancellation percent associated with the policy.
     */
    public function cancellationAdditionPercent()
    {
        return $this->belongsTo('App\Models\CancellationPercent', 'addition_percent_id', 'id');
    }
}
