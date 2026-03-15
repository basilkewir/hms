<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Only force HTTPS when the request actually arrived over HTTPS
        // (Cloudflare Tunnel sets X-Forwarded-Proto: https)
        // Local HTTP access is left untouched so both work simultaneously
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }
    }
}
