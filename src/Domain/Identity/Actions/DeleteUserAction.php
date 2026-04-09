<?php

declare(strict_types=1);

namespace Domain\Identity\Actions;

use Domain\Identity\Models\User;

final class DeleteUserAction
{
    public function execute(User $user): bool
    {
        return $user->delete();
    }
}
