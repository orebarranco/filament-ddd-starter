<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\DataTransferObjects\UserData;
use Domain\Identity\Models\User;

final class CreateUserAction
{
    public function execute(UserData $data): User
    {
        return User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
        ]);
    }
}
