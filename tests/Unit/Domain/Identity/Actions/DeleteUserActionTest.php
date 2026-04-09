<?php

declare(strict_types=1);

use Domain\Identity\Actions\DeleteUserAction;
use Domain\Identity\Models\User;

it('deletes the user from the database', function (): void {
    $user = User::factory()->create();

    resolve(DeleteUserAction::class)->execute($user);

    $this->assertDatabaseMissing(User::class, [
        'id' => $user->id,
    ]);
});

it('returns true when the user is deleted', function (): void {
    $user = User::factory()->create();

    $result = resolve(DeleteUserAction::class)->execute($user);

    expect($result)->toBeTrue();
});
