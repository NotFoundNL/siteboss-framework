<?php

namespace NotFound\Framework\Providers;

use NotFound\Framework\Events\AfterSaveEvent;
use NotFound\Framework\Events\BeforeSaveEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        BeforeSaveEvent::class => [
            //BeforeSaveListener::class,
        ],
        AfterSaveEvent::class => [
            //SitebossAfterSaveListener::class,
        ],
    ];
}
