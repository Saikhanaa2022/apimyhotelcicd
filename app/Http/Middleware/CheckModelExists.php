<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\{
    Block,
    Charge,
    Currency,
    CurrencyClone,
    DailyRate,
    DayRate,
    Group,
    Guest,
    GuestClone,
    Hotel,
    Interval,
    Item,
    Partner,
    PartnerClone,
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

class CheckModelExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $hotel = $request->user()
            ->hotel;

        if ($request->filled('block.id')) {
            
        }

        if ($request->filled('charge.id')) {
            
        }
        
        if ($request->filled('currency.id')) {

        }
        
        if ($request->filled('currencyClone.id')) {

        }
        
        if ($request->filled('dailyRate.id')) {

        }
        
        if ($request->filled('dayRate.id')) {

        }
        
        if ($request->filled('group.id')) {

        }
        
        if ($request->filled('guest.id')) {

        }
        
        if ($request->filled('guestClone.id')) {

        }
        
        if ($request->filled('hotel.id')) {

        }
        
        if ($request->filled('interval.id')) {

        }
        
        if ($request->filled('item.id')) {

        }
        
        if ($request->filled('partner.id')) {

        }
        
        if ($request->filled('partnerClone.id')) {

        }
        
        if ($request->filled('payment.id')) {

        }
        
        if ($request->filled('paymentMethod.id')) {

        }
        
        if ($request->filled('paymentMethodClone.id')) {

        }
        
        if ($request->filled('permission.id')) {

        }
        
        if ($request->filled('rate.id')) {

        }
        
        if ($request->filled('ratePlan.id')) {

        }
        
        if ($request->filled('ratePlanClone.id')) {

        }
        
        if ($request->filled('reservation.id')) {

        }
        
        if ($request->filled('room.id')) {

        }
        
        if ($request->filled('roomClone.id')) {

        }
        
        if ($request->filled('roomType.id')) {

        }
        
        if ($request->filled('roomTypeClone.id')) {

        }
        
        if ($request->filled('service.id')) {

        }
        
        if ($request->filled('serviceCategory.id')) {

        }
        
        if ($request->filled('serviceCategoryClone.id')) {

        }
        
        if ($request->filled('serviceClone.id')) {

        }
        
        if ($request->filled('source.id')) {

        }
        
        if ($request->filled('sourceClone.id')) {

        }
        
        if ($request->filled('tax.id')) {

        }
        
        if ($request->filled('taxClone.id')) {

        }
        
        if ($request->filled('user.id')) {

        }
        
        if ($request->filled('userClone.id')) {

        }

        return $next($request);
    }
}
