<?php

declare(strict_types=1);

use App\Policies\UserPolicy;
use Domain\Identity\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

it('resolves the UserPolicy through the Gate instead of falling back to null', function (): void {
    expect(Gate::getPolicyFor(User::class))->toBeInstanceOf(UserPolicy::class);
});

it('denies panel access to a user with no roles', function (): void {
    $user = User::factory()->create();

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('grants panel access to an active user holding a role', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('editor', 'web'));

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});

it('denies panel access to an inactive user even when it holds a role', function (): void {
    $user = User::factory()->inactive()->create();
    $user->assignRole(Role::findOrCreate('editor', 'web'));

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('identifies a super admin by the role name configured for Shield', function (): void {
    $superAdmin = User::factory()->superAdmin()->create();
    $plain = User::factory()->create();

    expect($superAdmin->isSuperAdmin())->toBeTrue()
        ->and($plain->isSuperAdmin())->toBeFalse();
});

it('casts active to a boolean', function (): void {
    $user = User::factory()->create();

    expect($user->active)->toBeBool()->toBeTrue();
});

it('lets only a super admin impersonate', function (): void {
    expect(User::factory()->superAdmin()->create()->canImpersonate())->toBeTrue()
        ->and(User::factory()->create()->canImpersonate())->toBeFalse();
});

it('can be impersonated when active and holding a role', function (): void {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('editor', 'web'));

    expect($user->canBeImpersonated())->toBeTrue();
});

it('cannot be impersonated without a role, since the swap would land on a 403', function (): void {
    expect(User::factory()->create()->canBeImpersonated())->toBeFalse();
});

it('cannot be impersonated while suspended', function (): void {
    $user = User::factory()->inactive()->create();
    $user->assignRole(Role::findOrCreate('editor', 'web'));

    expect($user->canBeImpersonated())->toBeFalse();
});

it('never lets one super admin be impersonated by another', function (): void {
    expect(User::factory()->superAdmin()->create()->canBeImpersonated())->toBeFalse();
});
