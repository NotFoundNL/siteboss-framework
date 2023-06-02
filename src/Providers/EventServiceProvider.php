<?php

namespace NotFound\Framework\Providers;

use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        //Should be true because Siteboss 'owns' the listen protected prop.
        return true;
    }
}
