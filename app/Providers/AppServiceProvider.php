<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\User;
use App\Models\Itinerary;
use App\Observers\OrderObserver;
use App\Observers\UserObserver;
use App\Observers\ItineraryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers
        Order::observe(OrderObserver::class);
        User::observe(UserObserver::class);
        Itinerary::observe(ItineraryObserver::class);
    }
}
