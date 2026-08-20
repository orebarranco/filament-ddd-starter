<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\ListUsers;
use Domain\Identity\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->superAdmin()->create());
});

it('can load the users list page', function (): void {
    $users = User::factory()->count(5)->create();

    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords($users);
});

it('can search users by name', function (): void {
    $users = User::factory()->count(5)->create();

    Livewire::test(ListUsers::class)
        ->searchTable($users->first()->name)
        ->assertCanSeeTableRecords($users->take(1))
        ->assertCanNotSeeTableRecords($users->skip(1));
});

it('can search users by email', function (): void {
    $users = User::factory()->count(5)->create();

    Livewire::test(ListUsers::class)
        ->searchTable($users->last()->email)
        ->assertCanSeeTableRecords($users->take(-1))
        ->assertCanNotSeeTableRecords($users->take($users->count() - 1));
});

it('can sort users by name', function (): void {
    $users = User::factory()->count(5)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users)
        ->sortTable('name')
        ->assertCanSeeTableRecords($users->sortBy('name'), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($users->sortByDesc('name'), inOrder: true);
});

it('can delete a user from the table', function (): void {
    $user = User::factory()->create();

    Livewire::test(ListUsers::class)
        ->callAction(TestAction::make('delete')->table($user))
        ->assertNotified();

    $this->assertDatabaseMissing(User::class, ['id' => $user->id]);
});
