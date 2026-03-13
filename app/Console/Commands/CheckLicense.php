<?php

namespace App\Console\Commands;

use App\Services\LicenseValidationService;
use Illuminate\Console\Command;

class CheckLicense extends Command
{
    protected $signature   = 'license:check {--force : Force online check even if recently validated}';
    protected $description = 'Periodically verify the active license against the license server';

    public function handle(LicenseValidationService $licenseService): int
    {
        $this->info('Checking license...');

        $valid = $licenseService->periodicCheck();

        if ($valid) {
            $this->info('License is valid.');
        } else {
            $this->warn('License check failed — system may be locked.');
        }

        return $valid ? Command::SUCCESS : Command::FAILURE;
    }
}
