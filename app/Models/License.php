<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class License extends Model
{
    protected $fillable = [
        'license_key',
        'hotel_id',
        'hotel_name',
        'license_type',
        'product_name',
        'customer_name',
        'customer_email',
        'organization',
        'max_devices',
        'current_devices',
        'max_rooms',
        'max_channels',
        'vod_enabled',
        'premium_features',
        'allowed_features',
        'features',
        'metadata',
        'assigned_rooms',
        'issued_at',
        'expires_at',
        'activated_at',
        'last_validated_at',
        'status',
        'hardware_fingerprint',
        'activation_code',
        'activation_count',
        'max_activations',
        'device_info',
        'notes',
        'license_data'
    ];

    protected $casts = [
        'vod_enabled' => 'boolean',
        'premium_features' => 'boolean',
        'allowed_features' => 'array',
        'features' => 'array',
        'metadata' => 'array',
        'device_info' => 'array',
        'license_data' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'last_validated_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_REVOKED = 'revoked';

    const LICENSE_TYPE_TRIAL = 'trial';
    const LICENSE_TYPE_BASIC = 'basic';
    const LICENSE_TYPE_PREMIUM = 'premium';
    const LICENSE_TYPE_ENTERPRISE = 'enterprise';
    const TYPE_PERPETUAL = 'perpetual';

    const DEVICE_TYPE_ANDROID_TV = 'android_tv';
    const DEVICE_TYPE_SMART_TV = 'smart_tv';
    const DEVICE_TYPE_MANAGEMENT_BACKEND = 'management_backend';
    const DEVICE_TYPE_ADMIN_PANEL = 'admin_panel';

    public function isValid(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->license_type === self::TYPE_PERPETUAL) {
            return true;
        }

        if ($this->expires_at !== null) {
            return $this->expires_at->isFuture();
        }

        return true;
    }

    public function isExpired(): bool
    {
        if ($this->license_type === self::TYPE_PERPETUAL) {
            return false;
        }

        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isPerpetual(): bool
    {
        return $this->license_type === self::TYPE_PERPETUAL;
    }

    public function isExpiringSoon($days = 30)
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->diffInDays(now()) <= $days;
    }

    public function canActivateDevice()
    {
        return $this->activation_count < $this->max_activations;
    }

    public function hasFeature($feature)
    {
        if (!$this->allowed_features) {
            return false;
        }

        return in_array($feature, $this->allowed_features);
    }

    public function canAddDevice(?string $deviceType = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return !$this->isDeviceLimitReached();
    }

    public function isDeviceLimitReached(): bool
    {
        return $this->licenseDevices()
            ->where('status', LicenseDevice::STATUS_ACTIVE)
            ->count() >= $this->max_devices;
    }

    public function licenseDevices()
    {
        return $this->hasMany(LicenseDevice::class, 'license_id');
    }

    public function validationLogs()
    {
        return $this->hasMany(LicenseValidationLog::class, 'license_id');
    }

    public function getAvailableFeatures(): array
    {
        if ($this->features !== null) {
            return is_array($this->features) ? $this->features : json_decode($this->features, true) ?? [];
        }

        return match ($this->license_type) {
            self::LICENSE_TYPE_TRIAL => ['live_tv', 'vod'],
            self::LICENSE_TYPE_BASIC => ['live_tv', 'vod', 'epg'],
            self::LICENSE_TYPE_PREMIUM => ['live_tv', 'vod', 'epg', 'favorites', 'watch_history'],
            self::LICENSE_TYPE_ENTERPRISE, self::TYPE_PERPETUAL => ['*'],
            default => [],
        };
    }

    public function incrementDeviceCount(?string $deviceType = null): void
    {
        $this->increment('current_devices');

        $normalized = $deviceType ? 'current_' . $this->normalizeDeviceType($deviceType) : null;
        if ($normalized && \Schema::hasColumn($this->getTable(), $normalized)) {
            $this->increment($normalized);
        }
    }

    private function normalizeDeviceType(string $deviceType): string
    {
        $mapping = [
            'android_tv' => 'android_tv',
            'smart_tv' => 'smart_tv',
            'management_backend' => 'backend',
            'admin_panel' => 'admin_panel'
        ];

        return $mapping[$deviceType] ?? $deviceType;
    }

    public function updateValidation(): void
    {
        $this->increment('validation_count');
        $this->update(['last_validated_at' => now()]);
    }

    public function activate($deviceInfo = null)
    {
        if (!$this->canActivateDevice()) {
            return false;
        }

        $this->update([
            'activated_at' => now(),
            'activation_count' => $this->activation_count + 1,
            'device_info' => $deviceInfo,
            'last_validated_at' => now()
        ]);

        return true;
    }

    public function validate()
    {
        $this->update(['last_validated_at' => now()]);
        return $this->isValid();
    }

    public function generateLicenseKey()
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
                         substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
                         substr(md5(uniqid(rand(), true)), 0, 8) . '-' .
                         substr(md5(uniqid(rand(), true)), 0, 8));
    }

    public function generateActivationCode()
    {
        return strtoupper(substr(md5(uniqid(rand(), true)), 0, 12));
    }

    public static function createLicense($data)
    {
        $license = new self();
        $license->license_key = $license->generateLicenseKey();
        $license->activation_code = $license->generateActivationCode();
        $license->issued_at = now();

        foreach ($data as $key => $value) {
            if (in_array($key, $license->fillable)) {
                $license->$key = $value;
            }
        }

        $license->save();
        return $license;
    }

    public function getRemainingDaysAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }

        return max(0, $this->expires_at->diffInDays(now()));
    }

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'active':
                return $this->isExpiringSoon() ? 'yellow' : 'green';
            case 'expired':
                return 'red';
            case 'suspended':
                return 'orange';
            case 'revoked':
                return 'red';
            default:
                return 'gray';
        }
    }
}
