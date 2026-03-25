<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ClearLicenseRateLimit extends Command
{
    protected $signature = 'license:clear-rate-limit {license_key? : Optional license key to clear only one key}';

    protected $description = 'Clear HMS-side license validation rate-limit cache keys';

    public function handle(): int
    {
        $licenseKey = $this->argument('license_key');

        if ($licenseKey) {
            $cacheKey = 'license_rate_limit_' . md5($licenseKey);
            Cache::forget($cacheKey);
            $this->info("Cleared rate-limit key for license: {$licenseKey}");
            return self::SUCCESS;
        }

        $driver = config('cache.default');

        try {
            if ($driver === 'database') {
                $deleted = DB::table(config('cache.stores.database.table', 'cache'))
                    ->where('key', 'like', 'license_rate_limit_%')
                    ->delete();

                $this->info("Cleared {$deleted} license rate-limit key(s) from database cache.");
                return self::SUCCESS;
            }

            if ($driver === 'redis') {
                $redis = app('redis')->connection(config('cache.stores.redis.connection', 'cache'));
                $keys = $redis->keys('license_rate_limit_*');

                if (!empty($keys)) {
                    $redis->del($keys);
                }

                $this->info('Cleared license rate-limit key(s) from Redis cache.');
                return self::SUCCESS;
            }

            $this->warn("Cache driver '{$driver}' does not support wildcard clear in this command.");
            $this->line('Use: php artisan license:clear-rate-limit YOUR-LICENSE-KEY');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Failed to clear rate limits: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
