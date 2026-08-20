<?php

declare(strict_types=1);

use Domain\Identity\Models\User;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('lets a guest ask for a reset link', function (): void {
    $this->get('/admin/password-reset/request')->assertSuccessful();
});

it('exposes the request page from the login page', function (): void {
    $this->get('/admin/login')
        ->assertSuccessful()
        ->assertSee(route('filament.admin.auth.password-reset.request'));
});

it('sends the reset notification to someone who can reach the panel', function (): void {
    Notification::fake();

    $user = User::factory()->superAdmin()->create();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($user, ResetPassword::class);
});

it('sends no link to a user that could not reach the panel anyway', function (): void {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertNothingSent();
});

it('sends no link to a suspended account', function (): void {
    Notification::fake();

    $user = User::factory()->inactive()->create();
    $user->assignRole(Role::findOrCreate('editor', 'web'));

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => $user->email])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertNothingSent();
});

it('reveals nothing when the address is not registered', function (): void {
    Notification::fake();

    Livewire::test(RequestPasswordReset::class)
        ->fillForm(['email' => 'nobody@example.com'])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertNothingSent();
});
