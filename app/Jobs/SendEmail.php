<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\{
    EmailInvoice, HotelActivated, HotelCreated, PasswordChanged,
    ReservationCreated, ResetPassword, SendReservationEmail,
    UserInvited, VerifyEmail, ResReqNotification, PaymentRequestNotification,
    EmailRoomTypeUpdate, EmailXroomReservation, XroomNotification
    // NotifyContactCreated,
};
use App\Models\User;
use Notification;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 20;

    protected $data;
    protected $object;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $object = null)
    {
        $this->data = $data;
        $this->object = $object;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $object = $this->object;

        // $locale = $data['locale'];
        $emailType = $data['emailType'];
        
        // Set user
        $user = new User;
        $user->email = $data['toEmail'];

        if ($emailType === 'hotelCreated') {
            Notification::send($user, new HotelCreated($object));
        } else if ($emailType === 'hotelActivated') {
            Notification::send($user, new HotelActivated($object, $data['state']));
        } else if ($emailType === 'passwordChanged') {
            Notification::send($user, new PasswordChanged($object));
        } else if ($emailType === 'reservationCreated') {
            Notification::send($user, new ReservationCreated($object));
        } else if ($emailType === 'invoiceCreated') {
            Notification::send($user, new EmailInvoice($object));
        } else if ($emailType === 'guestReservation') {
            Notification::send($user, new SendReservationEmail(
                $data['dataId'],
                $data['emailDocType'],
                $data['isGroup']
            ));
        } else if ($emailType === 'userInvited') {
            Notification::send($user, new UserInvited($user));
        } else if ($emailType === 'resReqCreated' || $emailType === 'resReqConfirmed' || $emailType === 'resReqPaid') {
            // Find user
            $user = User::whereEmail($data['toEmail'])->first();
            // Notify to user
            if (!is_null($user)) {
                $user->notify(new ResReqNotification($data, $object));
            }
        } else if ($emailType === 'notifyPayment') {
            Notification::send($user, new PaymentRequestNotification($data, $object));
        } else if ($emailType === 'roomTypeUpdate') {
            // Find user
            $user = User::whereEmail($data['toEmail'])->first();
            // Notify to user
            if (!is_null($user)) {
                Notification::send($user, new EmailRoomTypeUpdate($data, $object));
            }
        } else if ($emailType === 'xroomConfirmReservation') {
            // Find user
            $user = User::whereEmail($data['toEmail'])->first();
            // Notify to user
            if (!is_null($user)) {
                Notification::send($user, new EmailXroomReservation($data, $object));
            }
        } 
        else if ($emailType === 'newReservationXroom') {
            // Find user
            $user = User::whereEmail($data['toEmail'])->first();
            // Notify to user
            if (!is_null($user)) {
                Notification::send($user, new XroomNotification($data, $object));
            }
        } else {
            dd('Something went wrong.');
        }
    }
}
