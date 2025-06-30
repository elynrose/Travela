<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;

class StripeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('stripe', function ($app) {
            Stripe::setApiKey(config('services.stripe.secret'));
            return new \Stripe\StripeClient(config('services.stripe.secret'));
        });
    }

    public function boot()
    {
        // Register Stripe webhook events
        $this->app['events']->listen('stripe.webhook.*', function ($event, $payload) {
            // Remove Log::channel and similar lines
        });
    }
} 