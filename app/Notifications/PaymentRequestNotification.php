<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class PaymentRequestNotification extends Notification
{
    use Queueable;

    public $data;
    public $object;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data, $object)
    {
        $this->data = $data;
        $this->object = $object;
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
        $object = $this->object;
        $data = $this->data;

        $hotel = $object->hotel;
        $account = $data['account'];
        $objectType = $data['objectType'];
        $objectNumber = '';

        // Build subject
        $subject = '';
        if ($objectType === 'resReq') {
            $objectNumber = 'RN-' . $object->sync_id;
            $subject = $objectNumber . ' дугаартай урьдчилсан';
            $objectType = 'Урьдчилсан захиалга';
        } else {
            $objectNumber = $object->number;
            $subject = $objectNumber . ' дугаартай';
            $objectType = 'Үндсэн захиалга';
        }
        $subject = $subject . ' захиалгын төлбөр татах хүсэлт ирлээ.';

        if (!is_null($account)) {
            $account = new HtmlString('<br>
                <b>Төлбөр төлөх дансны мэдээлэл</b>:<hr>
                <br><b>Банк</b>: ' . $account['bank'] . '
                <br><b>Дансны нэр</b>: ' . $account['name'] . '
                <br><b>Дансны дугаар</b>: ' . $account['number'] . '
                <br><b>Валют</b>: ' . $account['currency']);
        }

        return (new MailMessage)
                    ->bcc(is_null($data['bccEmails']) ? [] : $data['bccEmails'])
                    ->subject($subject)
                    ->greeting('Сайн байна уу?')
                    ->line(new HtmlString('<br><b>Буудал</b>: ' . $hotel->name))
                    ->line(new HtmlString('<b>Захиалгын төрөл</b>: ' . $objectType))
                    ->line(new HtmlString('<b>Захиалга дугаар</b>: ' . $objectNumber))
                    ->line(new HtmlString('<b>Төлбөр</b>: ' . number_format($object->amount) . ' MNT'))
                    ->line(new HtmlString($account));
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
