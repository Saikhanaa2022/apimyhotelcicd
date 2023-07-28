<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Block,
    Currency,
    CurrencyClone,
    DailyRate,
    DayRate,
    Group,
    GuestClone,
    Guest,
    Hotel,
    Interval,
    Item,
    PartnerClone,
    Partner,
    PaymentMethodClone,
    PaymentMethod,
    Payment,
    Permission,
    Rate,
    RatePlanClone,
    RatePlan,
    Reservation,
    RoomClone,
    Room,
    RoomTypeClone,
    RoomType,
    ServiceCategoryClone,
    ServiceCategory,
    ServiceClone,
    Service,
    SourceClone,
    Source,
    Tax,
    TaxClone,
    UserClone,
    User
};
use App\Observers\{
    BlockObserver,
    CurrencyObserver,
    CurrencyCloneObserver,
    DailyRateObserver,
    DayRateObserver,
    GroupObserver,
    GuestCloneObserver,
    GuestObserver,
    HotelObserver,
    IntervalObserver,
    ItemObserver,
    PartnerCloneObserver,
    PartnerObserver,
    PaymentMethodCloneObserver,
    PaymentMethodObserver,
    PaymentObserver,
    PermissionObserver,
    RateObserver,
    RatePlanCloneObserver,
    RatePlanObserver,
    ReservationObserver,
    RoomCloneObserver,
    RoomObserver,
    RoomTypeCloneObserver,
    RoomTypeObserver,
    ServiceCategoryCloneObserver,
    ServiceCategoryObserver,
    ServiceCloneObserver,
    ServiceObserver,
    SourceCloneObserver,
    SourceObserver,
    TaxObserver,
    TaxCloneObserver,
    UserCloneObserver,
    UserObserver
};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::withoutDoubleEncoding();
        Block::observe(BlockObserver::class);
        Currency::observe(CurrencyObserver::class);
        CurrencyClone::observe(CurrencyCloneObserver::class);
        DailyRate::observe(DailyRateObserver::class);
        DayRate::observe(DayRateObserver::class);
        Group::observe(GroupObserver::class);
        GuestClone::observe(GuestCloneObserver::class);
        Guest::observe(GuestObserver::class);
        Hotel::observe(HotelObserver::class);
        Interval::observe(IntervalObserver::class);
        Item::observe(ItemObserver::class);
        PartnerClone::observe(PartnerCloneObserver::class);
        Partner::observe(PartnerObserver::class);
        PaymentMethodClone::observe(PaymentMethodCloneObserver::class);
        PaymentMethod::observe(PaymentMethodObserver::class);
        Payment::observe(PaymentObserver::class);
        Permission::observe(PermissionObserver::class);
        Rate::observe(RateObserver::class);
        RatePlanClone::observe(RatePlanCloneObserver::class);
        RatePlan::observe(RatePlanObserver::class);
        Reservation::observe(ReservationObserver::class);
        RoomClone::observe(RoomCloneObserver::class);
        Room::observe(RoomObserver::class);
        RoomTypeClone::observe(RoomTypeCloneObserver::class);
        RoomType::observe(RoomTypeObserver::class);
        ServiceCategoryClone::observe(ServiceCategoryCloneObserver::class);
        ServiceCategory::observe(ServiceCategoryObserver::class);
        ServiceClone::observe(ServiceCloneObserver::class);
        Service::observe(ServiceObserver::class);
        SourceClone::observe(SourceCloneObserver::class);
        Source::observe(SourceObserver::class);
        Tax::observe(TaxObserver::class);
        TaxClone::observe(TaxCloneObserver::class);
        UserClone::observe(UserCloneObserver::class);
        User::observe(UserObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        require_once __DIR__ . '/../helpers.php';
    }
}
