<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

class EmailServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Set up email queue monitoring
        Queue::failing(function (JobFailed $event) {
            // Remove Log::error and similar lines
        });
    }
} 