<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cookie;

class RedirectExpiredCsrfToken extends ValidateCsrfToken
{
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            $response = redirect()->route('login')
                ->with('error', 'Your session expired. Please sign in again.');

            $response->withCookie(Cookie::forget('XSRF-TOKEN'));
            $response->withCookie(Cookie::forget(config('session.cookie')));

            return $response;
        }
    }
}
