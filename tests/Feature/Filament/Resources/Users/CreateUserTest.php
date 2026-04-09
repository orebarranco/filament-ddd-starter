<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\CreateUser;
use Domain\Identity\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('can load the create user page', function (): void {
    Livewire::test(CreateUser::class)
        ->assertOk();
});

it('can create a user', function (): void {
    $newUser = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => $newUser->name,
            'email' => $newUser->email,
            'password' => 'password123',
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseHas(User::class, [
        'name' => $newUser->name,
        'email' => $newUser->email,
    ]);
});

it('validates the create form data', function (array $data, array $errors): void {
    $newUser = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => $newUser->name,
            'email' => $newUser->email,
            'password' => 'password123',
            ...$data,
        ])
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified()
        ->assertNoRedirect();
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    'email is required' => [['email' => null], ['email' => 'required']],
    'email must be valid' => [['email' => 'not-an-email'], ['email' => 'email']],
    'email max 255 characters' => [['email' => Str::random(256).'@example.com'], ['email' => 'max']],
    'password is required on create' => [['password' => null], ['password' => 'required']],
    'password max 255 characters' => [['password' => Str::random(256)], ['password' => 'max']],
]);

it('can assign roles when creating a user', function (): void {
    $role = Role::create(['name' => 'editor']);
    $newUser = User::factory()->make();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => $newUser->name,
            'email' => $newUser->email,
            'password' => 'password123',
            'roles' => [$role->id],
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    $created = User::query()->where('email', $newUser->email)->first();
    expect($created->hasRole('editor'))->toBeTrue();
});

it('validates that email is unique', function (): void {
    $existingUser = User::factory()->create();

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => $existingUser->email,
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique'])
        ->assertNotNotified();
});
