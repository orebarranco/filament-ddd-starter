<?php

declare(strict_types=1);

use Domain\Identity\DataTransferObjects\UserData;

it('builds itself from a form array', function (): void {
    $data = UserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'secret_password',
        'active' => true,
    ]);

    expect($data->name)->toBe('John Doe')
        ->and($data->email)->toBe('john@example.com')
        ->and($data->password)->toBe('secret_password')
        ->and($data->active)->toBeTrue();
});

it('leaves the password null when the key is absent', function (): void {
    $data = UserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($data->password)->toBeNull();
});

it('leaves the password null when the edit form submits the field blank', function (): void {
    $data = UserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => '',
    ]);

    expect($data->password)->toBeNull();
});

it('defaults active to true when the key is absent', function (): void {
    $data = UserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    expect($data->active)->toBeTrue();
});

it('keeps active false when the form says so', function (): void {
    $data = UserData::fromArray([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'active' => false,
    ]);

    expect($data->active)->toBeFalse();
});

it('defaults password to null and active to true through the constructor', function (): void {
    $data = new UserData(name: 'John Doe', email: 'john@example.com');

    expect($data->password)->toBeNull()
        ->and($data->active)->toBeTrue();
});
