<?php

namespace App\Providers;

use App\Services\KhanbankService;
use App\Services\QPayService;
use Illuminate\Support\ServiceProvider;

class QPayServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(QPayService::class, function ($app) {
            return new QPayService();
        });

        $this->app->singleton(KhanbankService::class, function ($app) {
            return new KhanbankService($app['config']->get('services.xroom.khanbank'));
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

    public function provides()
    {
        return [QPayService::class, KhanbankService::class];
    }
}