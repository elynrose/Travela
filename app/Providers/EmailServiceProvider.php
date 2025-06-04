<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

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
            Log::error('Email job failed', [
                'job' => get_class($event->job),
                'exception' => $event->exception->getMessage(),
                'queue' => $event->job->getQueue(),
            ]);
        });
    }
} 