<?php

namespace NotFound\Framework\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Define global log data
     */
    public function boot()
    {
        // BUG: Neither value is logged...
        Log::withContext([
            'user-id' => Auth::user()?->sub,
            'ip' => request()->ip(),
        ]);
    }
}
