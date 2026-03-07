<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class License extends Model
{
    protected $fillable = [
        'license_key',
        'license_type',
        'product_name',
        'customer_name',
        'customer_email',
        'organization',
        'max_devices',
        'max_rooms',
        'max_channels',
        'vod_enabled',
        'premium_features',
        'allowed_features',
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
        'device_info' => 'array',
        'license_data' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'last_validated_at' => 'datetime',
    ];

    public function isValid()
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            $this->update(['status' => 'expired']);
            return false;
        }

        return true;
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
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
