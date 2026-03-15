<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountantReportOverride extends Model
{
    protected $fillable = ['user_id', 'report_type', 'metric_key', 'custom_value'];

    /**
     * Apply stored overrides to a data array.
     * Supports dot-notation keys like "profitLossData.total_revenue".
     */
    public static function applyOverrides(int $userId, string $reportType, array $data): array
    {
        $overrides = static::where('user_id', $userId)
            ->where('report_type', $reportType)
            ->get();

        foreach ($overrides as $override) {
            $value = json_decode($override->custom_value, true) ?? $override->custom_value;
            static::setNestedValue($data, $override->metric_key, $value);
        }

        return $data;
    }

    /**
     * Set a value in a nested array using dot notation.
     */
    private static function setNestedValue(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $ref = &$array;
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $ref[$k] = $value;
            } else {
                if (!isset($ref[$k]) || !is_array($ref[$k])) {
                    $ref[$k] = [];
                }
                $ref = &$ref[$k];
            }
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
