<?php

declare(strict_types=1);

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Database\Seeders\ShieldSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('seeds exactly the permissions Shield discovers for the panel, so the snapshot cannot drift', function (): void {
    $this->seed(ShieldSeeder::class);

    $seeded = Permission::query()->pluck('name')->sort()->values()->all();
    $discovered = collect(FilamentShield::getEntitiesPermissions())->sort()->values()->all();

    expect($seeded)->toBe($discovered);
});

it('can run twice without duplicating a permission or the super admin role', function (): void {
    $this->seed(ShieldSeeder::class);
    $this->seed(ShieldSeeder::class);

    expect(Permission::query()->count())->toBe(count(FilamentShield::getEntitiesPermissions()))
        ->and(Role::query()->where('name', 'super_admin')->count())->toBe(1);
});

it('leaves the super admin role holding no permissions, since the gate answers for it', function (): void {
    $this->seed(ShieldSeeder::class);

    expect(Role::query()->where('name', 'super_admin')->sole()->permissions)->toBeEmpty();
});
