<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // ✅ ADD THIS

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ FORCE HTTPS IN PRODUCTION
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
