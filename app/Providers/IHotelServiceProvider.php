<?php

namespace App\Providers;

use App\Models\Reservation;
use Illuminate\Support\ServiceProvider;
use App\Services\ReservationService;

class IHotelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton(ReservationService::class, function ($app) {
            return new ReservationService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
