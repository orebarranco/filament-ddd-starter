<?php

declare(strict_types=1);

use Database\Seeders\SuperAdminSeeder;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

it('leaves a fresh install with an administrator that can reach the panel', function (): void {
    $this->seed(SuperAdminSeeder::class);

    $admin = User::query()->where('email', 'admin@example.com')->sole();

    expect($admin->isSuperAdmin())->toBeTrue()
        ->and($admin->active)->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeTrue();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();
});

it('can run twice without duplicating the administrator or its role', function (): void {
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $admins = User::query()->where('email', 'admin@example.com')->get();

    expect($admins)->toHaveCount(1)
        ->and($admins->first()->roles)->toHaveCount(1);
});
