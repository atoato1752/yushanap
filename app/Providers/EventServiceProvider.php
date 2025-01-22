<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\PaymentSucceeded::class => [
            \App\Listeners\UpdateQueryStatus::class,
            \App\Listeners\CreateAgentEarning::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
} 