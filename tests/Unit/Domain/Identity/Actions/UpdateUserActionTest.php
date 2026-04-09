<?php

declare(strict_types=1);

use Domain\Identity\Actions\UpdateUserAction;
use Domain\Identity\DataTransferObjects\UserData;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the user name and email', function (): void {
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $data = new UserData(
        name: 'Updated Name',
        email: 'updated@example.com',
    );

    resolve(UpdateUserAction::class)->execute($user, $data);

    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('updates the password when provided', function (): void {
    $user = User::factory()->create();

    $data = new UserData(
        name: $user->name,
        email: $user->email,
        password: 'new_password',
    );

    $updatedUser = resolve(UpdateUserAction::class)->execute($user, $data);

    expect(Hash::check('new_password', $updatedUser->password))->toBeTrue();
});

it('does not change the password when not provided', function (): void {
    $user = User::factory()->create(['password' => 'original_password']);
    $originalPasswordHash = $user->password;

    $data = new UserData(
        name: 'New Name',
        email: $user->email,
    );

    resolve(UpdateUserAction::class)->execute($user, $data);

    $this->assertDatabaseHas(User::class, [
        'id' => $user->id,
        'password' => $originalPasswordHash,
    ]);
});

it('returns the updated User model', function (): void {
    $user = User::factory()->create();

    $data = new UserData(
        name: 'New Name',
        email: 'new@example.com',
    );

    $result = resolve(UpdateUserAction::class)->execute($user, $data);

    expect($result)
        ->toBeInstanceOf(User::class)
        ->and($result->name)->toBe('New Name')
        ->and($result->email)->toBe('new@example.com');
});
