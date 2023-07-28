<?php

namespace App\Models;
use Illuminate\Support\Carbon;

class CancellationPolicyClone extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cancellation_policy_clones';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_free' => 'boolean',
        'has_prepayment' => 'boolean',
        'summaries' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'summaries',
        'cancellationPayment',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cancellation_policy_id', 'cancellation_time_id', 'cancellation_percent_id', 'is_free', 'addition_percent_id', 'has_prepayment'
    ];

    /**
     * Get policy summaries
     */
    public function getSummariesAttribute() {
        $reservation = $this->reservation()->first();
        $summaries = [];

        if (!$reservation->is_time) {
            $cancellationPercent = $this->cancellationPercent()->first();
            $cancellationAdditionPercent = $this->cancellationAdditionPercent()->first();
            $cancellationTime = $this->cancellationTime()->first();

            $checkIn = Carbon::parse($reservation->check_in)->format('Y-m-d');
            $firstNight = $reservation->dayRates()->first()->value;

            // Free cancellation
            if ($this->is_free) {
                $title = 'Үнэгүй цуцлалт';
                $dayBefore = Carbon::parse($checkIn)->subDays($cancellationTime->day)->format('Y/m/d');

                // Check has time
                if ($cancellationTime->has_time) {
                    $str = $checkIn . '-ний ' . $cancellationTime->day . ' цаг хүртэл.';
                    $strNotFree = Carbon::parse($checkIn)->format('Y/m/d') . '-ний ' . $cancellationTime->day . ' цагаас хойш';
                } else {
                    $dayBefore = Carbon::parse($checkIn)->subDays($cancellationTime->day + 1)->format('Y/m/d');
                    $str =  $dayBefore . ' 23:59 хүртэл';
                    $strNotFree = $dayBefore . '-ний 23:59 -аас хойш';
                }

                array_push($summaries, ['title' => $title, 'subtitle' => $str, 'color' => 'green', 'amount' => 0]);

                if ($cancellationPercent->is_first_night) {
                    $title = 'Эхний шөнийн төлбөр';
                    $amount = $firstNight;
                } else {
                    $title = 'Нийт үнийн ' . $cancellationPercent->percent .'%';
                    $amount = ceil($reservation->amount / 100 * $cancellationPercent->percent);
                }

                array_push($summaries, ['title' => $title, 'subtitle' => $strNotFree, 'color' => 'red', 'amount' => $amount]);

                // No show
                if ($cancellationAdditionPercent->is_first_night) {
                    $title = 'Эхний шөнийн төлбөр';
                    $amount = $firstNight;
                } else {
                    $title = 'Нийт үнийн ' . $cancellationAdditionPercent->percent .'% торгууль төлнө';
                    $amount = ceil($reservation->amount / 100 * $cancellationAdditionPercent->percent);
                }

                array_push($summaries, ['title' => 'Ирээгүй тохиолдолд', 'subtitle' => $title, 'color' => 'red', 'amount' => $amount]);
            } else {
                // Not free cancellation
                if ($cancellationTime) {
                    $dayBefore = Carbon::parse($checkIn)->subDays($cancellationTime->day)->format('Y/m/d');

                    if ($cancellationPercent->is_first_night) {
                        $title = 'Эхний шөнийн төлбөр';
                        $amount = $firstNight;
                    } else {
                        $title = 'Нийт үнийн ' . $cancellationPercent->percent .'%';
                        $amount = ceil($reservation->amount / 100 * $cancellationPercent->percent);
                    }

                    $str = Carbon::parse($checkIn)->subDays($cancellationTime->day + 1)->format('Y/m/d') . ' 23:59 хүртэл';
                    array_push($summaries, ['title' => $title, 'subtitle' => $str, 'color' => 'red', 'amount' => $amount]);


                    if ($cancellationAdditionPercent->is_first_night) {
                        $title = 'Эхний шөнийн төлбөр';
                        $amount = $firstNight;
                    } else {
                        $title = 'Нийт үнийн ' . $cancellationAdditionPercent->percent .'%';
                        $amount = ceil($reservation->amount / 100 * $cancellationAdditionPercent->percent);
                    }

                    $str = Carbon::parse($checkIn)->subDays($cancellationTime->day)->format('Y/m/d') . ' -аас хойш';
                    array_push($summaries, ['title' => $title, 'subtitle' => $str, 'color' => 'red', 'amount' => $amount]);

                } else {
                    $title = 'Цуцлах тохиолдолд';

                    if ($cancellationPercent->is_first_night) {
                        $str = 'Эхний шөнийн төлбөр';
                        $amount = $firstNight;
                    } else {
                        $str = 'Нийт үнийн ' . $cancellationPercent->percent .'% торгууль төлнө';
                        $amount = ceil($reservation->amount / 100 * $cancellationPercent->percent);
                    }

                    array_push($summaries, ['title' => $title, 'subtitle' => $str, 'color' => 'red', 'amount' => $amount]);
                }
            }

            // Prepayment policy
            if ($this->has_prepayment) {
                $title = 'Урьдчилсан төлбөр авна';
            } else {
                $title = 'Урьдчилсан төлбөр авахгүй';
            }

            array_push($summaries, ['title' => $title, 'subtitle' => '', 'color' => 'blue']);
        }

        return $summaries;
    }

    /**
     * Get cancellation policy payment
     */
    public function getCancellationPaymentAttribute() {
        $reservation = $this->reservation()->first();
        //dd($reservation);
        $cancellationPayment = 0;
        if (!$reservation->is_time) {
            $cancellationPercent = $this->cancellationPercent()->first();
            $cancellationAdditionPercent = $this->cancellationAdditionPercent()->first();
            $cancellationTime = $this->cancellationTime()->first();
            // Төлбөр гарсан эсэхийг хугацаан дээрээс тооцоолж мэдэх
            // Хичнээн хэмжээний төлбөр гарсаныг цуцлалтын бодлогоос тооцох
            $dateNow = Carbon::now();
            $checkIn = Carbon::parse($reservation->check_in)->format('Y-m-d');
            $parsedCheckIn = Carbon::parse($checkIn);

            // Reservation first nigth payment
            $firstNight = $reservation->dayRates()->first()->value;
            $cancellationPayment = $firstNight;

            if ($this->is_free) {
                // Цаг тохриуулагдсан байвал
                if ($cancellationTime->has_time) {
                    $checkIn = $checkIn . ' ' . $cancellationTime->day . ':00';
                    $parsedFreeDay = Carbon::parse($checkIn);
                } else {
                    $parsedFreeDay = Carbon::parse($checkIn)->subDays($cancellationTime->day + 1);
                }

                // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
                if ($dateNow->gt($parsedCheckIn)) {
                    // No Show cancellation
                    if ($cancellationAdditionPercent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = ceil($reservation->amount / 100 * $cancellationAdditionPercent->percent);
                    }
                }

                // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
                if ($parsedFreeDay->gt($dateNow)) {
                    // Free cancellation
                    $cancellationPayment = 0;
                }

                // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах хугацааг хэтэрсэн мөн ирэх ёстой хугацаанаас өмнө байх тохиолдолд
                // тодорхой заасан төлбөрийг тооцож авна
                if ($parsedCheckIn->gt($parsedFreeDay) && $parsedCheckIn->gt($dateNow)) {
                    // First Night cancellation
                    if ($cancellationPercent->is_first_night) {
                        $cancellationPayment = $firstNight;
                    } else {
                        $cancellationPayment = ceil($reservation->amount / 100 * $cancellationPercent->percent);
                    }
                }
            } else {
                if ($cancellationTime) {
                    // Цаг тохриуулагдсан байвал
                    $parsedFreeDay = Carbon::parse($checkIn)->subDays($cancellationTime->day + 1);

                    // Тухайн өдөр нь зочин ирэх өдрөөс хэтэрсэн байвал хугацаандаа амжиж ирээгүй болно
                    if ($dateNow->gt($parsedCheckIn)) {
                        // No Show cancellation
                        if ($cancellationAdditionPercent->is_first_night) {
                            $cancellationPayment = $firstNight;
                        } else {
                            $cancellationPayment = ceil($reservation->amount / 100 * $cancellationAdditionPercent->percent);
                        }
                    }

                    // Тухайн өдөр нь зочин захиалгаа үнэгүй цуцлах боломжтой өдөр хүртэл байвал үнэгүй цуцлана
                    if ($parsedFreeDay->gt($dateNow)) {
                        // Free cancellation
                        if ($cancellationPercent->is_first_night) {
                            $cancellationPayment = $firstNight;
                        } else {
                            $cancellationPayment = ceil($reservation->amount / 100 * $cancellationPercent->percent);
                        }
                    }
                } else {
                    if (!$cancellationPercent->is_first_night) {
                        $cancellationPayment = ceil($reservation->amount / 100 * $cancellationPercent->percent);
                    }
                }
            }
        }

        return $cancellationPayment;
    }

    /**
     * Get the cancellation policy associated with the clone.
     */
    public function cancellationPolicy()
    {
        return $this->belongsTo('App\Models\CancellationPolicy');
    }

    /**
     * Get the reservation that owns the clone.
     */
    public function reservation()
    {
        return $this->hasOne('App\Models\Reservation');
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
