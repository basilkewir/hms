<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FetchWeather
 *
 * Fetches current weather from OpenWeatherMap and caches the result in
 * Laravel's cache (+ a settings row so it survives restarts).
 *
 * Run manually:
 *   php artisan weather:fetch
 *
 * Scheduled every 15 minutes in routes/console.php.
 */
class FetchWeather extends Command
{
    protected $signature   = 'weather:fetch';
    protected $description = 'Fetch current weather from OpenWeatherMap and cache it for IPTV devices';

    public function handle(): int
    {
        // ── Read config from settings table ───────────────────────────────
        $rows = Setting::whereIn('key', ['weather_api_key', 'weather_city', 'weather_units', 'weather_enabled'])
            ->pluck('value', 'key')
            ->toArray();

        $enabled = filter_var($rows['weather_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $apiKey  = $rows['weather_api_key'] ?? '';
        $city    = $rows['weather_city']    ?? '';
        $units   = $rows['weather_units']   ?? 'metric';

        if (!$enabled) {
            $this->info('Weather widget is disabled — skipping.');
            return self::SUCCESS;
        }

        if (empty($apiKey) || empty($city)) {
            $this->warn('weather_api_key or weather_city not configured in settings — skipping.');
            return self::FAILURE;
        }

        // ── Call OpenWeatherMap ───────────────────────────────────────────
        $url = "https://api.openweathermap.org/data/2.5/weather";

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()   // bypass SSL in local/dev environments
                ->get($url, [
                    'q'     => $city,
                    'appid' => $apiKey,
                    'units' => $units,
                ]);

            if ($response->failed()) {
                $this->error("OpenWeatherMap returned HTTP {$response->status()}: " . $response->body());
                Log::warning('weather:fetch HTTP error', ['status' => $response->status()]);
                return self::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['main'])) {
                $this->error('Unexpected response — no main key: ' . json_encode($data));
                return self::FAILURE;
            }

            // ── Build the weather payload ─────────────────────────────────
            $unitSymbol = $units === 'metric' ? '°C' : '°F';

            $weather = [
                'city'        => $data['name']           ?? $city,
                'country'     => $data['sys']['country'] ?? '',
                'temperature' => round($data['main']['temp']      ?? 0),
                'feels_like'  => round($data['main']['feels_like'] ?? 0),
                'humidity'    => (int)($data['main']['humidity'] ?? 0),
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon'        => $data['weather'][0]['icon'] ?? '',
                'icon_url'    => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '@2x.png',
                'unit_symbol' => $unitSymbol,
                'units'       => $units,
                'wind_speed'  => round($data['wind']['speed'] ?? 0),
                'fetched_at'  => now()->toIso8601String(),
            ];

            // ── Store in Laravel cache (30 min TTL as safety net) ─────────
            Cache::put('weather_data', $weather, now()->addMinutes(30));

            // ── Also persist in settings so it survives cache:clear ───────
            Setting::updateOrCreate(
                ['key' => 'weather_cache'],
                ['value' => json_encode($weather)]
            );

            $this->info(sprintf(
                '✅ Weather updated: %s, %s → %d%s, %s (humidity %d%%)',
                $weather['city'],
                $weather['country'],
                $weather['temperature'],
                $unitSymbol,
                $weather['description'],
                $weather['humidity']
            ));

            Log::info('weather:fetch success', $weather);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            Log::error('weather:fetch exception', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }
    }
}
