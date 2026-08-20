<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Spatie\Permission\Models\Role;

it('redirects a guest to the login page', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('keeps out an authenticated user that holds no role', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('keeps out a suspended user even when it holds a role', function (): void {
    $user = User::factory()->inactive()->create();
    $user->assignRole(Role::findOrCreate('editor', 'web'));

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('lets a super admin reach the panel', function (): void {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get('/admin')
        ->assertSuccessful();
});
