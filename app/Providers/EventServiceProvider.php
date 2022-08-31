<?php

namespace App\Providers;

use App\Events\HouseCollecetionCreatedEvent;
use App\Events\HouseCollecetionDeletedEvent;
use App\Events\HouseCollecetionUpdatedEvent;
use App\Listeners\HouseCollecetionCreatedListener;
use App\Listeners\HouseCollecetionDeletedListener;
use App\Listeners\HouseCollecetionUpdatedListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
        HouseCollecetionCreatedEvent::class => [
            HouseCollecetionCreatedListener::class
        ],
        HouseCollecetionUpdatedEvent::class => [
            HouseCollecetionUpdatedListener::class
        ],
        HouseCollecetionDeletedEvent::class => [
            HouseCollecetionDeletedListener::class
        ],
        'Illuminate\Database\Events\QueryExecuted' => [
            'App\Listeners\QueryListener'
        ],

    ];
}
