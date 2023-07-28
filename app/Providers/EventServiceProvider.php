<?php

namespace App\Providers;

use App\Events\{
    ContactCreated,
    HotelCreated,
    HotelActivated,
    UserInvited,
    PasswordChanged,
    EmailInvoice,
    ReservationEmailSend,
    ReservationCreated,
    ReservationUpdated,
    RoomTypeUpdated
};
use App\Events\XRoomInvoiceConfirmed;
use App\Events\XRoomReservationCreated;
use App\Listeners\{
    SendEmailContactCreated,
    SendEmailNewHotelRegistered,
    SendEmailHotelActivated,
    SendEmailUserInvited,
    SendEmailPasswordChanged,
    SendEmailInvoice,
    SendReservationEmail,
    SendReservationCreatedEmail,
    SyncCreatedReservation,
    SyncUpdatedReservation,
    SyncUpdatedRoomType
};
use App\Listeners\CreateXRoomReservation;
use App\Listeners\SendPaymentToHotel;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ContactCreated::class => [
            SendEmailContactCreated::class
        ],
        Registered::class => [
            SendEmailVerificationNotification::class
        ],
        HotelCreated::class => [
            SendEmailNewHotelRegistered::class
        ],
        HotelActivated::class => [
            SendEmailHotelActivated::class
        ],
        UserInvited::class => [
            SendEmailUserInvited::class
        ],
        PasswordChanged::class => [
            SendEmailPasswordChanged::class
        ],
        EmailInvoice::class => [
            SendEmailInvoice::class
        ],
        ReservationEmailSend::class => [
            SendReservationEmail::class
        ],
        ReservationCreated::class => [
            SyncCreatedReservation::class,
            SendReservationCreatedEmail::class
        ],
        ReservationUpdated::class => [
            SyncUpdatedReservation::class
        ],
        RoomTypeUpdated::class => [
            SyncUpdatedRoomType::class
        ],
        XRoomInvoiceConfirmed::class => [
            CreateXRoomReservation::class
        ],
        XRoomReservationCreated::class => [
            SendPaymentToHotel::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}