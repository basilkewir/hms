<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Dynamically set APP_URL and scheme based on the incoming request.
        // This allows the app to work on both:
        //   - http://10.0.0.10  (local network)
        //   - https://donzebemanagement.qzz.io  (Cloudflare Tunnel)
        // Cloudflare Tunnel sets X-Forwarded-Proto: https on tunneled requests.

        $isHttps = request()->server('HTTP_X_FORWARDED_PROTO') === 'https'
            || request()->server('HTTPS') === 'on';

        if ($isHttps) {
            URL::forceScheme('https');

            // Mark session cookie as Secure so browsers send it over HTTPS
            // (without this, XSRF-TOKEN cookie is blocked and every request gets 419)
            Config::set('session.secure', true);

            // Set APP_URL to the actual public hostname so Ziggy and
            // CSRF token validation use the correct origin
            $host = request()->server('HTTP_X_FORWARDED_HOST')
                ?? request()->server('HTTP_HOST')
                ?? parse_url(config('app.url'), PHP_URL_HOST);

            Config::set('app.url', 'https://' . $host);
            URL::forceRootUrl('https://' . $host);
        }
    }
}
