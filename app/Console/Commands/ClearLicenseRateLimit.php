<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearLicenseRateLimit extends Command
{
    protected $signature = 'license:clear-rate-limit {license_key?}';

    protected $description = 'Clear license validation rate limiting to allow immediate retry';

    public function handle()
    {
        $licenseKey = $this->argument('license_key');

        if ($licenseKey) {
            $rateLimitKey = 'license_rate_limit_' . md5($licenseKey);
            Cache::forget($rateLimitKey);
            $this->info("Rate limit cleared for license key: {$licenseKey}");
        } else {
            // Clear all license rate limiting keys
            $pattern = 'license_rate_limit_*';
            // Redis backend
            try {
                Cache::getStore()->connection()->del(
                    Cache::getStore()->connection()->keys('license_rate_limit_*')
                );
                $this->info('All license rate limits cleared');
            } catch (\Exception $e) {
                // Fallback for file/array cache
                $this->warn('Could not clear all rate limits. Please specify a license key or restart the application.');
                $this->info("Usage: php artisan license:clear-rate-limit {your-license-key}");
            }
        }
    }
}
