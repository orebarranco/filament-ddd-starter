<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\DataTransferObjects\UserData;
use Domain\Identity\Models\User;

final class UpdateUserAction
{
    public function execute(User $user, UserData $data): User
    {
        $attributes = [
            'name' => $data->name,
            'email' => $data->email,
            'active' => $data->active,
        ];

        if ($data->password !== null) {
            $attributes['password'] = $data->password;
        }

        $user->update($attributes);

        return $user->fresh();
    }
}
