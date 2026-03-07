<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class CheckoutPermissionSeeder extends Seeder
{
    public function run()
    {
        // Find the admin role
        $adminRole = Role::where('name', 'admin')->first();

        // Find the process_checkouts permission
        $checkoutPermission = Permission::where('name', 'process_checkouts')->first();

        if ($adminRole && $checkoutPermission) {
            // Assign permission to admin role if not already assigned
            if (!$adminRole->hasPermission($checkoutPermission)) {
                $adminRole->givePermission($checkoutPermission);
                $this->command->info("Successfully assigned 'process_checkouts' permission to admin role.");
            } else {
                $this->command->info("Permission 'process_checkouts' is already assigned to admin role.");
            }
        } else {
            $this->command->error("Admin role or process_checkouts permission not found.");
        }
    }
}
