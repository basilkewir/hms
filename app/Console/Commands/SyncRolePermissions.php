<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class SyncRolePermissions extends Command
{
    protected $signature = 'roles:sync-permissions';
    protected $description = 'Sync permissions for all roles based on their default permissions';

    public function handle()
    {
        $this->info('Syncing role permissions...');
        
        $defaultRoles = Role::getDefaultRoles();
        
        foreach ($defaultRoles as $roleName => $roleData) {
            $role = Role::where('name', $roleName)->first();
            
            if (!$role) {
                $this->warn("Role '{$roleName}' not found, skipping...");
                continue;
            }
            
            if (!isset($roleData['permissions']) || empty($roleData['permissions'])) {
                $this->warn("Role '{$roleName}' has no default permissions defined, skipping...");
                continue;
            }
            
            // Get permission IDs
            $permissionIds = Permission::whereIn('name', $roleData['permissions'])->pluck('id');
            
            if ($permissionIds->isEmpty()) {
                $this->warn("No permissions found for role '{$roleName}', creating missing permissions...");
                
                // Create missing permissions
                foreach ($roleData['permissions'] as $permName) {
                    $permission = Permission::firstOrCreate(
                        ['name' => $permName],
                        [
                            'display_name' => ucwords(str_replace('_', ' ', $permName)),
                            'category' => 'System',
                            'description' => 'Auto-generated permission'
                        ]
                    );
                    $permissionIds->push($permission->id);
                }
            }
            
            // Sync permissions
            $role->permissions()->sync($permissionIds);
            $this->info("Synced {$permissionIds->count()} permissions to role '{$roleName}'");
        }
        
        $this->info('Role permissions synced successfully!');
        return 0;
    }
}
