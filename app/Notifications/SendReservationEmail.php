<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

use App\Models\{Group, Reservation};

class SendReservationEmail extends Notification
{
    use Queueable;

    protected $dataId;
    protected $emailType;
    protected $isGroup;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($dataId, $emailType, $isGroup)
    {
        $this->dataId = $dataId;
        $this->emailType = $emailType;
        $this->isGroup = $isGroup;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Check is send email by group
        if ($this->isGroup) {
            $group = Group::find($this->dataId);
            $firstRes = $group->reservations[0];

            if ($this->emailType === 'registration') {
                $subject = "Бүртгэлийн хуудас";
                $today = Carbon::today()->format('Y-m-d');

                return (new MailMessage)
                            ->subject($subject)
                            ->markdown('mail.group.reservationReg', [
                                'group' => $group,
                                'guest' => $firstRes->guestClone,
                                'hotel' => $firstRes->hotel,
                                'today' => $today
                            ]);
            } else if ($this->emailType === 'reservation') {
                $subject = "Захиалгын хуудас";
                $today = Carbon::today()->format('Y-m-d');
                $totalPrice = 0;

                foreach ($firstRes->dayRates as $d) {
                    $totalPrice += $d->value;
                }

                return (new MailMessage)
                            ->subject($subject)
                            ->markdown('mail.group.confirmationLetter', [
                                'hotel' => $firstRes->hotel,
                                'hotelSetting' => $firstRes->hotel->hotelSetting,
                                'group' => $group,
                                'guest' => $firstRes->guestClone,
                                'today' => $today,
                                'totalPrice' => $totalPrice
                            ]);
            } else if ($this->emailType === 'payment') {
                $subject = "Захиалгын төлбөрийн дэлгэрэнгүй";
                $items = [];
                $totalAmount = 0;
                $totalAmountTax = 0;
                $firstRes = $group->reservations[0];

                foreach ($group->reservations as $res) {
                    $totalAmount += $res->occupancyAmount() + $res->itemsAmount() + $res->extraBedsAmount();
                    $totalAmountTax += $res->amount;
                    $quantity = $res->is_time ? 1 : $res->stay_nights;

                    // Push room
                    $items[] = [
                        'date' => Carbon::parse($res->check_in)->format('y/m/d' . ($res->is_time ? ' H:m' : '')) . ' - ' . Carbon::parse($res->check_out)->format('y/m/d' . ($res->is_time ? ' H:m' : '')),
                        'name' => $res->roomTypeClone->name,
                        'quantity' => $quantity,
                        'price' => $res->occupancyAmount() / $quantity,
                        'totalPrice' => $res->occupancyAmount()
                    ];

                    // Push services
                    foreach ($res->items as $item) {
                        $items[] = [
                            'date' => Carbon::parse($item->created_at)->format('y/m/d H:m'),
                            'name' => $item->serviceCategoryClone->name . ' - ' . $item->serviceClone->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'totalPrice' => $item->price * $item->quantity
                        ];
                    }

                    // Push extra beds
                    foreach ($res->extraBeds as $item) {
                        $items[] = [
                            'date' => Carbon::parse($item->created_at)->format('y/m/d H:m'),
                            'name' => 'Нэмэлт ор',
                            'quantity' => $item->nights,
                            'price' => $item->amount,
                            'totalPrice' => $item->amount * $item->nights
                        ];
                    }
                }

                return (new MailMessage)
                            ->subject($subject)
                            ->markdown('mail.group.paymentDetail', [
                                'groupNumber' => $group->number,
                                'guest' => $firstRes->guestClone,
                                'items' => $items,
                                'taxes' => $firstRes->taxClones,
                                'totalAmount' => $totalAmount,
                                'totalAmountTax' => $totalAmountTax
                            ]);
            }
        } else {
            $reservation = Reservation::find($this->dataId);

            if ($this->emailType === 'registration') {
                $subject = "Бүртгэлийн хуудас";
                $today = Carbon::today()->format('Y-m-d');

                return (new MailMessage)
                            ->subject($subject)
                            ->markdown('mail.reservation.reservationReg', [
                                'hotel' => $reservation->hotel,
                                'reservation' => $reservation,
                                'guest' => $reservation->guestClone,
                                'today' => $today
                            ]);
            } else if ($this->emailType === 'reservation') {
                $subject = "Захиалгын хуудас";
                $today = Carbon::today()->format('Y-m-d');

                return (new MailMessage)
                            ->subject($subject)
                            ->markdown('mail.reservation.confirmationLetter', [
                                'hotel' => $reservation->hotel,
                                'hotelSetting' => $reservation->hotel->hotelSetting,
                                'reservation' => $reservation,
                                'today' => $today,
                                'roomTotalPrice' => $reservation->occupancyAmount()
                            ]);
            } else if ($this->emailType === 'payment') {
                $subject = "Захиалгын төлбөрийн дэлгэрэнгүй";
                $roomTotalPrice = 0;
                $totalAmount = 0;
                $totalAmountTax = 0;

                $roomTotalPrice = $reservation->occupancyAmount();
                $totalAmount = $roomTotalPrice + $reservation->itemsAmount() + $reservation->extraBedsAmount();
                $totalAmountTax = $totalAmount;

                foreach ($reservation->taxClones as $tax) {
                    if (!$tax->inclusive) {
                        $totalAmountTax += $totalAmount * ($tax->percentage / 100);
                    }
                }

                return (new MailMessage)
                            ->subject($subject)
                            ->markdown('mail.reservation.paymentDetail', [
                                'reservation' => $reservation,
                                'roomTotalPrice' => $roomTotalPrice,
                                'totalAmount' => $totalAmount,
                                'totalAmountTax' => $totalAmountTax,
                            ]);
            } else {
                return (new MailMessage)
                        ->line('The introduction to the notification.')
                        ->action('Notification Action', url('/'))
                        ->line('Thank you for using our application!');
            }
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
