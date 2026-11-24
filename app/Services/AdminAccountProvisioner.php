<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAccountProvisioner
{
    public const EMAIL = 'admin@hotel.com';

    /**
     * Create the initial admin account once, without changing an existing account.
     *
     * @return array{created: bool, email: string, password: ?string}
     */
    public function provision(): array
    {
        return DB::transaction(function (): array {
            if (User::query()->where('email', self::EMAIL)->lockForUpdate()->exists()) {
                return [
                    'created' => false,
                    'email' => self::EMAIL,
                    'password' => null,
                ];
            }

            $password = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

            $adminRole = Role::query()->firstOrCreate(
                ['name' => 'admin'],
                [
                    'display_name' => 'Administrator',
                    'description' => 'Full system access and management',
                    'is_active' => true,
                ]
            );

            $user = User::query()->create([
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'email' => self::EMAIL,
                'password' => Hash::make($password),
                'department' => 'Administration',
                'position' => 'System Administrator',
                'employment_status' => 'active',
                'is_active' => true,
            ]);

            $user->roles()->syncWithoutDetaching([
                $adminRole->id => ['assigned_at' => now()],
            ]);

            return [
                'created' => true,
                'email' => self::EMAIL,
                'password' => $password,
            ];
        });
    }
}
