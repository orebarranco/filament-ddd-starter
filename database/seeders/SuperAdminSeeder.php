<?php

declare(strict_types=1);

namespace Database\Seeders;

use Domain\Identity\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Without this seeder a fresh install lands on a panel nobody can enter: User
 * requires a role to pass canAccessPanel(), and Shield ships no roles of its
 * own. super_admin bypasses every permission check through Shield's gate
 * interceptor, so this one role is enough to get in and assign the rest.
 *
 * The credentials below are development defaults. Change them before seeding
 * anything reachable from outside your machine.
 */
final class SuperAdminSeeder extends Seeder
{
    private const string EMAIL = 'admin@example.com';

    private const string PASSWORD = 'password';

    public function run(): void
    {
        resolve(PermissionRegistrar::class)->forgetCachedPermissions();

        $roleName = config()->string('filament-shield.super_admin.name', 'super_admin');

        Role::findOrCreate($roleName, config()->string('auth.defaults.guard'));

        $user = User::query()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Super Admin',
                // Hashed by the model's `password` cast, not here.
                'password' => self::PASSWORD,
                'active' => true,
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole($roleName);
    }
}
