<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stripe\Stripe;
use Illuminate\Support\Facades\Log;

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
        // Set up Stripe logging
        Stripe::setLogger(Log::channel('stripe'));

        // Register Stripe webhook events
        $this->app['events']->listen('stripe.webhook.*', function ($event, $payload) {
            Log::channel('stripe')->info('Stripe webhook event', [
                'event' => $event,
                'payload' => $payload
            ]);
        });
    }
} 