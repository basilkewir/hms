<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\License;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TrialLicenseSeeder extends Seeder
{
    /**
     * Seed a 14-day trial license for new installations.
     * The trial allows full system access without online validation.
     * After 14 days, a valid online license key must be activated.
     */
    public function run(): void
    {
        // Do not overwrite an existing trial or real license
        if (License::whereIn('status', ['trial', 'active'])->exists()) {
            $this->command->info('License already exists — skipping trial seed.');
            return;
        }

        $trialKey  = 'TRIAL-' . strtoupper(Str::random(8));
        $expiresAt = Carbon::now()->addDays(14);

        License::create([
            'license_key'     => $trialKey,
            'license_type'    => 'TRIAL',
            'product_name'    => 'Hotel Management System',
            'customer_name'   => 'Trial User',
            'customer_email'  => 'admin@hotel.com',
            'status'          => 'trial',
            'issued_at'       => Carbon::now(),
            'expires_at'      => $expiresAt,
            'activated_at'    => Carbon::now(),
            'last_validated_at' => Carbon::now(),
            'activation_count'  => 0,
            'max_activations'   => 1,
            'notes'           => '14-day trial license. Activate a valid license key from kewirdev.com before expiry.',
            'license_data'    => [
                'license_type'   => 'TRIAL',
                'status'         => 'TRIAL',
                'expires_at'     => $expiresAt->toDateTimeString(),
                'trial_days'     => 14,
                'is_trial'       => true,
                'is_valid'       => true,
                'features'       => [
                    'max_channels'    => -1,
                    'max_users'       => -1,
                    'analytics'       => true,
                    'custom_branding' => false,
                    'api_access'      => false,
                ],
            ],
        ]);

        $this->command->info(
            '✓ 14-day trial license created. Expires: ' . $expiresAt->format('Y-m-d') .
            "\n  Activate a real license at: /license/activate"
        );
    }
}
