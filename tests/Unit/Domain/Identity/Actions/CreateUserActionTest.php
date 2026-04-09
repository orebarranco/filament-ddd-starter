<?php

declare(strict_types=1);

use Domain\Identity\Actions\CreateUserAction;
use Domain\Identity\DataTransferObjects\UserData;
use Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates a user in the database', function (): void {
    $data = new UserData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
    );

    resolve(CreateUserAction::class)->execute($data);

    $this->assertDatabaseHas(User::class, [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('returns the created User model', function (): void {
    $data = new UserData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'password123',
    );

    $user = resolve(CreateUserAction::class)->execute($data);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->exists)->toBeTrue()
        ->and($user->id)->toBeInt();
});

it('hashes the password when creating the user', function (): void {
    $data = new UserData(
        name: 'John Doe',
        email: 'john@example.com',
        password: 'secret_password',
    );

    $user = resolve(CreateUserAction::class)->execute($data);

    expect(Hash::check('secret_password', $user->password))->toBeTrue();
});
