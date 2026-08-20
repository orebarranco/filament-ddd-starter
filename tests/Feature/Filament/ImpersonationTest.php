<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\ListUsers;
use Domain\Identity\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

it('offers the action to a super admin over a user that can reach the panel', function (): void {
    $this->actingAs(User::factory()->superAdmin()->create());

    $target = User::factory()->create();
    $target->assignRole(Role::findOrCreate('editor', 'web'));

    Livewire::test(ListUsers::class)
        ->assertActionVisible(TestAction::make('impersonate')->table($target));
});

it('hides the action over a user that could not reach the panel anyway', function (): void {
    $this->actingAs(User::factory()->superAdmin()->create());

    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('impersonate')->table(User::factory()->create()));
});

it('hides the action over another super admin', function (): void {
    $this->actingAs(User::factory()->superAdmin()->create());

    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('impersonate')->table(User::factory()->superAdmin()->create()));
});
