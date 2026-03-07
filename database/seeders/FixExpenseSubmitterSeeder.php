<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\User;

class FixExpenseSubmitterSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first()
            ?? User::first();

        if (!$admin) {
            $this->command->warn('No user found. Skipping.');
            return;
        }

        $count = Expense::whereNull('submitted_by')->update(['submitted_by' => $admin->id]);

        $name = trim($admin->first_name . ' ' . $admin->last_name);
        $this->command->info("Updated {$count} expense(s) — submitted_by set to: {$name} (ID: {$admin->id})");
    }
}
