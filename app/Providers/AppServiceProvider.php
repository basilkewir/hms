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
        // HMS and the Android client use the hotel LAN HTTP endpoint. Force all
        // generated URLs and session cookies to that origin so opening the admin
        // panel through an HTTPS proxy cannot advertise an unreachable HTTPS
        // server address to TVs.
        $root = rtrim((string) config('app.url'), '/');
        if ($root === '') {
            $root = 'http://192.168.100.130';
        }

        URL::forceScheme('http');
        URL::forceRootUrl($root);
        Config::set('app.url', $root);
        Config::set('session.secure', false);
        Config::set('session.domain', null);
    }
}
