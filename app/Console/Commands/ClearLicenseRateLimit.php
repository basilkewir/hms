<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
            $this->info("✓ Rate limit cleared for license key: {$licenseKey}");
            $this->info("  You can now try activating your license again.");
            return 0;
        }

        // Clear all license rate limits requires knowing cache driver
        $cacheDriver = config('cache.default');
        
        try {
            if ($cacheDriver === 'redis') {
                // Redis: delete by pattern
                $redis = Cache::connection('redis')->client();
                $keys = $redis->keys('license_rate_limit_*');
                if (!empty($keys)) {
                    $redis->del($keys);
                    $this->info("✓ Cleared " . count($keys) . " license rate limit keys from Redis");
                } else {
                    $this->info("✓ No license rate limits found in Redis");
                }
            } elseif ($cacheDriver === 'database') {
                // Database cache: delete from cache table
                $deleted = DB::table('cache')
                    ->where('key', 'like', 'license_rate_limit_%')
                    ->delete();
                $this->info("✓ Cleared $deleted license rate limit keys from database cache");
            } elseif ($cacheDriver === 'file') {
                // File cache: would need to scan filesystem, not practical
                $this->warn('Cannot clear all file-based cache keys automatically.');
                $this->info('Please specify a license key: php artisan license:clear-rate-limit YOUR-KEY');
                return 1;
            } else {
                // Other drivers
                $this->warn("Cache driver '{$cacheDriver}' is not supported for bulk clearing.");
                $this->info('Please specify a license key: php artisan license:clear-rate-limit YOUR-KEY');
                return 1;
            }
            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->info('Please try specifying a license key instead:');
            $this->info('  php artisan license:clear-rate-limit YOUR-LICENSE-KEY');
            return 1;
        }
    }
}
