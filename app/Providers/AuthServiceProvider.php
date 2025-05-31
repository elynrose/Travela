<?php

namespace App\Providers;

use App\Models\Itinerary;
use App\Policies\ItineraryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\PayoutRequest;
use App\Policies\PayoutRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Itinerary::class => ItineraryPolicy::class,
        PayoutRequest::class => PayoutRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 