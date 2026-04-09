<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\EditUser;
use Domain\Identity\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('can load the edit user page', function (): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $user->name,
            'email' => $user->email,
        ]);
});

it('can update a user', function (): void {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
        ])
        ->call('save')
        ->assertNotified();

    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => $newData->name,
        'email' => $newData->email,
    ]);
});

it('can update the user password', function (): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new_secure_password',
        ])
        ->call('save')
        ->assertNotified();

    expect(Hash::check('new_secure_password', $user->fresh()->password))->toBeTrue();
});

it('does not change the password if the field is left empty', function (): void {
    $user = User::factory()->create(['password' => 'original_password']);
    $originalHash = $user->getAttributes()['password'];

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
        ])
        ->call('save')
        ->assertNotified();

    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'password' => $originalHash,
    ]);
});

it('can delete a user from the edit page', function (): void {
    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->callAction(DeleteAction::class)
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseMissing(User::class, ['id' => $user->id]);
});

it('can assign roles when editing a user', function (): void {
    $user = User::factory()->create();
    $role = Role::create(['name' => 'manager']);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm(['roles' => [$role->id]])
        ->call('save')
        ->assertNotified();

    expect($user->fresh()->hasRole('manager'))->toBeTrue();
});

it('can remove roles when editing a user', function (): void {
    $role = Role::create(['name' => 'manager']);
    $user = User::factory()->create();
    $user->assignRole($role);

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm(['roles' => []])
        ->call('save')
        ->assertNotified();

    expect($user->fresh()->hasRole('manager'))->toBeFalse();
});

it('validates the edit form data', function (array $data, array $errors): void {
    $user = User::factory()->create();
    $newData = User::factory()->make();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            ...$data,
        ])
        ->call('save')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'name max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    'email is required' => [['email' => null], ['email' => 'required']],
    'email must be valid' => [['email' => 'not-an-email'], ['email' => 'email']],
    'email max 255 characters' => [['email' => Str::random(256).'@example.com'], ['email' => 'max']],
]);

it('validates that email is unique ignoring the current record', function (): void {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->id])
        ->fillForm([
            'name' => $user->name,
            'email' => $otherUser->email,
        ])
        ->call('save')
        ->assertHasFormErrors(['email' => 'unique'])
        ->assertNotNotified();
});
