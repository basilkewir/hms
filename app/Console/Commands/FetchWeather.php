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
 * Fetches current weather and caches the result in Laravel's cache
 * (+ a settings row so it survives restarts).
 *
 * Primary source: OpenWeatherMap (needs a weather_api_key setting).
 * Fallback source: Open-Meteo (free, no API key required) — used
 * automatically when no weather_api_key is configured.
 *
 * Run manually:
 *   php artisan weather:fetch
 *
 * Scheduled every 15 minutes in routes/console.php.
 */
class FetchWeather extends Command
{
    protected $signature   = 'weather:fetch';
    protected $description = 'Fetch current weather and cache it for IPTV devices';

    public function handle(): int
    {
        // ── Read config from settings table ───────────────────────────────
        $rows = Setting::whereIn('key', ['weather_api_key', 'weather_city', 'weather_units', 'weather_enabled'])
            ->pluck('value', 'key')
            ->toArray();

        $enabled = filter_var($rows['weather_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $apiKey  = $rows['weather_api_key'] ?? '';
        $city    = trim((string)($rows['weather_city'] ?? ''));
        $units   = $rows['weather_units'] ?? 'metric';

        if (!$enabled) {
            $this->info('Weather widget is disabled — skipping.');
            return self::SUCCESS;
        }

        if (empty($city)) {
            $this->warn('weather_city not configured in settings — skipping.');
            return self::FAILURE;
        }

        // Prefer a configured OpenWeatherMap key; otherwise fall back to the
        // free Open-Meteo API (no key required) so the widget works out of the box.
        $weather = !empty($apiKey)
            ? $this->fetchFromOpenWeatherMap($city, $apiKey, $units)
            : $this->fetchFromOpenMeteo($city, $units);

        if (!$weather) {
            return self::FAILURE;
        }

        // ── Store in Laravel cache (30 min TTL as safety net) ─────────────
        Cache::put('weather_data', $weather, now()->addMinutes(30));

        // ── Also persist in settings so it survives cache:clear ───────────
        Setting::updateOrCreate(
            ['key' => 'weather_cache'],
            ['value' => json_encode($weather)]
        );

        $this->info(sprintf(
            '✅ Weather updated: %s, %s → %d%s, %s (humidity %d%%)',
            $weather['city'],
            $weather['country'],
            $weather['temperature'],
            $weather['unit_symbol'],
            $weather['description'],
            $weather['humidity']
        ));

        Log::info('weather:fetch success', $weather);

        return self::SUCCESS;
    }

    /**
     * Fetch weather from OpenWeatherMap (requires weather_api_key).
     */
    private function fetchFromOpenWeatherMap(string $city, string $apiKey, string $units): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withoutVerifying()   // bypass SSL in local/dev environments
                ->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q'     => $city,
                    'appid' => $apiKey,
                    'units' => $units,
                ]);

            if ($response->failed()) {
                $this->error("OpenWeatherMap returned HTTP {$response->status()}: " . $response->body());
                Log::warning('weather:fetch HTTP error', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            if (!isset($data['main'])) {
                $this->error('Unexpected OpenWeatherMap response — no main key: ' . json_encode($data));
                return null;
            }

            $unitSymbol = $units === 'imperial' ? '°F' : '°C';

            return [
                'city'        => $data['name']           ?? $city,
                'country'     => $data['sys']['country'] ?? '',
                'temperature' => round($data['main']['temp']      ?? 0),
                'feels_like'  => round($data['main']['feels_like'] ?? 0),
                'humidity'    => (int)($data['main']['humidity'] ?? 0),
                'description' => ucfirst($data['weather'][0]['description'] ?? ''),
                'icon'        => $data['weather'][0]['icon'] ?? '01d',
                'icon_url'    => 'https://openweathermap.org/img/wn/' . ($data['weather'][0]['icon'] ?? '01d') . '@2x.png',
                'unit_symbol' => $unitSymbol,
                'units'       => $units,
                'wind_speed'  => round($data['wind']['speed'] ?? 0),
                'fetched_at'  => now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            $this->error('OpenWeatherMap exception: ' . $e->getMessage());
            Log::error('weather:fetch exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch weather from Open-Meteo (free, no API key required).
     */
    private function fetchFromOpenMeteo(string $city, string $units): ?array
    {
        try {
            // 1) Geocode the city name to coordinates
            $geo = Http::timeout(10)
                ->withoutVerifying()
                ->get('https://geocoding-api.open-meteo.com/v1/search', [
                    'name'     => $city,
                    'count'    => 1,
                    'language' => 'en',
                    'format'   => 'json',
                ]);

            $results = $geo->successful() ? ($geo->json('results') ?? []) : [];

            if (empty($results)) {
                $this->error('Open-Meteo geocoding failed for "' . $city . '": ' . $geo->body());
                return null;
            }

            $place   = $results[0];
            $lat     = $place['latitude']  ?? null;
            $lon     = $place['longitude'] ?? null;
            $name    = $place['name']      ?? $city;
            $country = $place['country']   ?? '';

            if ($lat === null || $lon === null) {
                $this->error('Open-Meteo geocoding returned no coordinates for "' . $city . '"');
                return null;
            }

            // 2) Current weather at that location
            $weather = Http::timeout(10)
                ->withoutVerifying()
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'  => $lat,
                    'longitude' => $lon,
                    'current'   => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                    'timezone'  => 'auto',
                ]);

            if ($weather->failed()) {
                $this->error('Open-Meteo weather request failed: ' . $weather->body());
                return null;
            }

            $data = $weather->json('current');
            if (!$data || !isset($data['temperature_2m'])) {
                $this->error('Open-Meteo returned unexpected payload: ' . $weather->body());
                return null;
            }

            $code        = (int)($data['weather_code'] ?? 0);
            [$description, $icon] = $this->openMeteoCodeToCondition($code);

            // Open-Meteo always reports metric (°C); convert for imperial units.
            $celsius = (float)$data['temperature_2m'];
            $display = $units === 'imperial' ? $celsius * 9 / 5 + 32 : $celsius;
            $unitSymbol = $units === 'imperial' ? '°F' : '°C';

            return [
                'city'        => $name,
                'country'     => $country,
                'temperature' => round($display),
                'feels_like'  => round($display),
                'humidity'    => (int)($data['relative_humidity_2m'] ?? 0),
                'description' => $description,
                'icon'        => $icon,
                'icon_url'    => 'https://openweathermap.org/img/wn/' . $icon . '@2x.png',
                'unit_symbol' => $unitSymbol,
                'units'       => $units,
                'wind_speed'  => round($data['wind_speed_10m'] ?? 0),
                'fetched_at'  => now()->toIso8601String(),
            ];

        } catch (\Exception $e) {
            $this->error('Open-Meteo exception: ' . $e->getMessage());
            Log::error('weather:fetch open-meteo exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Map Open-Meteo WMO weather codes to a human description + OWM-style icon.
     */
    private function openMeteoCodeToCondition(int $code): array
    {
        $map = [
            0  => ['Clear sky', '01d'],
            1  => ['Mainly clear', '01d'],
            2  => ['Partly cloudy', '02d'],
            3  => ['Overcast', '03d'],
            45 => ['Fog', '50d'],
            48 => ['Rime fog', '50d'],
            51 => ['Light drizzle', '09d'],
            53 => ['Drizzle', '09d'],
            55 => ['Dense drizzle', '09d'],
            56 => ['Freezing drizzle', '13d'],
            57 => ['Freezing drizzle', '13d'],
            61 => ['Light rain', '10d'],
            63 => ['Rain', '10d'],
            65 => ['Heavy rain', '10d'],
            66 => ['Freezing rain', '13d'],
            67 => ['Freezing rain', '13d'],
            71 => ['Light snow', '13d'],
            73 => ['Snow', '13d'],
            75 => ['Heavy snow', '13d'],
            77 => ['Snow grains', '13d'],
            80 => ['Rain showers', '09d'],
            81 => ['Rain showers', '09d'],
            82 => ['Violent rain showers', '09d'],
            85 => ['Snow showers', '13d'],
            86 => ['Snow showers', '13d'],
            95 => ['Thunderstorm', '11d'],
            96 => ['Thunderstorm with hail', '11d'],
            99 => ['Thunderstorm with hail', '11d'],
        ];

        return $map[$code] ?? ['Unknown', '02d'];
    }
}
